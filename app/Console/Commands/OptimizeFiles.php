<?php

namespace App\Console\Commands;

use App\Jobs\MoveFileJob;
use App\Models\Contractor;
use App\Models\File;
use App\Models\RequirementContent;
use App\Models\RequirementHistory;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Log;

class OptimizeFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensures files are moved to S3 buckets, and runs some tests';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->moveAllFiles();

        $this->verifyFileLocations();

        $this->requirementHistories();

        $this->logStatistics();
    }

    private function logStatistics()
    {
        Log::debug("Getting statistics");

        // NOTE: Adding in where to make it a query builder
        $queryAllFiles = File::whereNotNull('name');

        $queryOrigAvatars = User::whereNotNull('avatar')
            ->where('avatar', '<>', '');
        $queryNewAvatars = User::whereNotNull('avatar_file_id');

        $queryOrigReqContents = RequirementContent::whereNotNull('file')
            ->where('file', '<>', '');
        $queryFileReqContents = RequirementContent::leftJoin('requirements', 'requirements.id', 'requirement_contents.requirement_id')
            ->where('requirements.content_type', 'file');
        $queryNewReqContents = RequirementContent::whereNotNull('file_id');

        $queryOrigReqHistory = RequirementHistory::whereNotNull('certificate_file')
            ->where('certificate_file', '<>', '');
        $queryNewReqHistory = RequirementHistory::whereNotNull('file_id');

        $queryCountProps = [
            [
                'query' => $queryAllFiles,
                'property' => "Count All Files",
            ],
            [
                'query' => $queryOrigAvatars,
                'property' => "Count Original Avatars",
            ],
            [
                'query' => $queryNewAvatars,
                'property' => "Count New Avatars",
            ],

            [
                'query' => $queryOrigReqContents,
                'property' => "Count Original Requirement Contents",
            ],
            [
                'query' => $queryFileReqContents,
                'property' => "Count of Requirement Contents that are File types",
            ],
            [
                'query' => $queryNewReqContents,
                'property' => "Count New Requirement Contents",
            ],

            [
                'query' => $queryOrigReqHistory,
                'property' => "Count Original Requirement History",
            ],
            [
                'query' => $queryNewReqHistory,
                'property' => "Count New Requirement History",
            ],
        ];

        Log::debug("Running queries and generating table");
        $resultCountProps = collect($queryCountProps)
            ->map(function ($row) {
                // Executing querries and returning property / value array
                $stat = $this->getQueryStat($row['query'], $row['property']);
                $count = $stat;
                try {
                    $count = sizeof($stat);
                } catch (Exception $e) {
                    $count = $stat;
                }
                return [
                    'property' => $row['property'],
                    'value' => $count,
                ];
            });

        $avatarCountStat = $this->getQueryDifference($queryOrigAvatars, $queryNewAvatars, 'Avatar Count');
        $reqContentStat = $this->getQueryDifference($queryFileReqContents, $queryNewReqContents, 'Requirement Content Count');
        $reqHistoryStat = $this->getQueryDifference($queryOrigReqHistory, $queryNewReqHistory, 'Requirement History Count');

        $resultStatistics = collect([
            [
                'property' => 'Avatar Difference Count',
                'value' => $avatarCountStat['difference'],
            ],
            [
                'property' => 'Avatar Difference %',
                'value' => $avatarCountStat['percent'],
            ],

            [
                'property' => 'Requirement Content Difference Count',
                'value' => $reqContentStat['difference'],
            ],
            [
                'property' => 'Requirement Content Difference %',
                'value' => $reqContentStat['percent'],
            ],

            [
                'property' => 'Requirement History Difference Count',
                'value' => $reqHistoryStat['difference'],
            ],
            [
                'property' => 'Requirement History Difference %',
                'value' => $reqHistoryStat['percent'],
            ],
        ]);

        $headers = ['property', 'value'];

        $rows = $resultCountProps
            ->merge([[
                'property' => '',
                'value' => '',
            ]])
            ->merge($resultStatistics)
            ->toArray();

        $this->table(
            $headers,
            $rows
        );
    }

    private function getQueryDifference(Builder $originalQuery, Builder $newQuery, String $property)
    {
        try {
            $originalResult = sizeof($originalQuery->get());
            $newResult = sizeof($newQuery->get());
            return [
                'difference' => $newResult - $originalResult,
                'percent' => round(($newResult - $originalResult) / $originalResult, 5) * 100,
            ];
        } catch (Exception $e) {
            Log::error(__METHOD__ . ': ' . $e->getMessage());
            return [
                'difference' => 'error',
                'percent' => 'error',
            ];
        }
    }

    private function getQueryStat(Builder $query, String $property)
    {
        try {
            $result = $query->get();
            return $result;
        } catch (Exception $e) {

            Log::warn("Cannot determine value for '$property'", [
                'message' => $e->getMessage(),
            ]);
            return "ERROR";
        }
    }

    private function moveAllFiles()
    {
        /** Limit of how many files before switching from dispatchNow to dispatch */
        $dispatchNowLimit = 10;

        // Getting files to move
        $publicDriveFiles = File::where('disk', 'public')->get();
        $countPublicDriveFiles = sizeof($publicDriveFiles);

        // End if no files to move
        if ($countPublicDriveFiles == 0) {
            Log::info("No files to move");
            return;
        }

        Log::info("Moving $countPublicDriveFiles Files");

        foreach ($publicDriveFiles as $file) {
            try {
                Log::debug("Moving file with Dispatch");
                MoveFileJob::dispatch($file, [
                    'path' => null,
                    'disk' => null,
                ]);
                // }
            } catch (Exception $e) {
                Log::error("Failed to move file $file->id.", [
                    "message" => $e->getMessage(),
                ]);
            }
        }

    }

    public function verifyFileLocations()
    {
        // Checking all file paths to make sure theyre correct
        $allFiles = File::get();

        foreach ($allFiles as $file) {
            if (dirname($file->path) != $file->getStoragePath()) {
                Log::warn("File paths do not match", [
                    // 'stored' => ($file->path),
                    'calculated' => ($file->getStoragePath()),
                    'dirname stored' => dirname($file->path),
                    // 'dirname calculated' => dirname($file->getStoragePath()),
                ]);

                MoveFileJob::dispatch($file, [
                    'path' => null,
                    'disk' => null,
                ]);
            }
        }
    }

    // Ensures the contractor_id is set
    public function requirementHistories()
    {
        Log::debug("Fixing Missing COntractor IDs in Requirement Histories");
        $histories = RequirementHistory::whereNull('contractor_id')
            ->whereNotNull('file_id')
            ->get();

        foreach ($histories as $history) {
            Log::debug("Trying");
            $role = $history->role;
            if (isset($role)) {
                $company = $role->company;
            }

            if (isset($company)) {
                if (get_class($company) == Contractor::class) {
                    Log::debug("Updating contractor_id for history $history->id to $company->id");
                    $history->update([
                        'contractor_id' => $company->id,
                    ]);
                }
            }
        }
    }
}
