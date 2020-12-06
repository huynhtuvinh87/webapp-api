<?php

use App\Jobs\MoveFileJob;
use App\Models\File;
use App\Models\RequirementHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ImplementRequirementHistoryS3Files extends Migration
{
    private $sampleSize = null;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Log::info(__METHOD__);
        try {
            DB::beginTransaction();

            // Create file_id column in users table
            Schema::table('requirement_histories', function (Blueprint $table) {
                // Dropping column if it already exists
                if (Schema::hasColumn('requirement_histories', 'file_id')) {
                    Schema::table('requirement_histories', function (Blueprint $table) {
                        $table->dropColumn('file_id');
                    });
                }

                $table->unsignedInteger('file_id')
                    ->nullable();
            });

            // Moving all files over
            if (isset($this->sampleSize)) {
                $allReqHistories = RequirementHistory::limit(100)
                    ->whereNotNull('certificate_file')
                    ->where('certificate_file', '<>', '')
                    ->get();
            } else {
                $allReqHistories = RequirementHistory::whereNotNull('certificate_file')
                    ->where('certificate_file', '<>', '')
                    ->get();
            }

            $countFilesToMove = sizeof($allReqHistories);
            $countFilesMoved = 0;
            $countFilesNotFound = 0;

            Log::info("Files to Move: $countFilesToMove");

            foreach ($allReqHistories as $history) {
                try {

                    $hasFile = $history->certificate_file != null;
                    $fileExists = false;

                    if ($hasFile) {
                        $fileExists = Storage::disk('public')->exists($history->certificate_file);
                    }

                    if (!$fileExists) {
                        $countFilesNotFound++;
                        Log::debug("Requirement history file not found", [
                            'certificate file' => $history->certificate_file,
                        ]);
                    }

                    // If file exists on the drive...
                    if ($fileExists) {
                        $role = $history->role;
                        if (!isset($role)) {
                            try {
                                $requirement = $history->requirement;
                                $hiringOrg = $requirement->hiring_organization;
                                $owner = $hiringOrg->owner;
                                $role = $owner->role;
                            } catch (Exception $e) {
                                // Role can't be determined through the requirement
                                $role = null;
                            }
                        }

                        $newFile = File::create([
                            'name' => $history->original_file_name,
                            'path' => $history->certificate_file,
                            'ip' => null,
                            'disk' => 'public',
                            'visibility' => 'public',
                        ]);

                        try {
                            if (isset($role)) {
                                $newFile->update([
                                    'role_id' => $role->id,
                                ]);
                            } else {
                                throw new Exception("Could not find owner for associated Hiring Org");
                            }
                        } catch (Exception $e) {
                            Log::error($e->getMessage());
                        }

                        try {
                            if (isset($history->created_at)) {
                                $newFile->update([
                                    'created_at' => $history->created_at,
                                ]);
                            }
                        } catch (Exception $e) {
                            // Do nothing - not important
                            Log::debug("Failed to update created_at for file", [
                                "Original created at" => $history->created_at,
                            ]);
                        }

                        $newFile->updateFromPath();

                        if (!isset($newFile->id)) {
                            throw new Exception("File ID was not defined - failed to create file for avatar");
                        }

                        RequirementHistory::where('id', $history->id)
                            ->update([
                                'file_id' => $newFile->id,
                            ]);

                        try {
                            MoveFileJob::dispatch($newFile, [
                                'path' => null,
                                'disk' => null,
                            ])
                                ->delay(now()->addMinutes(10));
                            $countFilesMoved++;

                        } catch (Exception $e) {
                            Log::warn("Failed to move file");
                            Log::warn($e->getMessage());
                        }
                    }
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                }
            }

            // Dropping columns
            // $this->dropColumnIfExists('requirement_histories', 'certificate_file');
            // $this->dropColumnIfExists('requirement_histories', 'original_file_name');
            // $this->dropColumnIfExists('requirement_histories', 'certificate_file_name');
            // $this->dropColumnIfExists('requirement_histories', 'certificate_file_ext');

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }

        Log::info("Files Moved: $countFilesMoved");
        Log::info("Files not Found: $countFilesNotFound");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    public function dropColumnIfExists($tableName, $columnName)
    {

        Schema::table($tableName, function (Blueprint $table) use ($columnName, $tableName) {
            // Dropping column if it already exists
            if (Schema::hasColumn($tableName, $columnName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                    $table->dropColumn($columnName);
                });
            }
        });
    }
}
