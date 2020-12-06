<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\User;
use Exception;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RestoreBackup extends Command
{
    protected $deletedBackups = [];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        try {
            $path = $this->getBackupPath();

            $this->restoreDB($path);

            $this->info("Backup Completed");

            if (sizeof($this->deletedBackups) > 0) {
                $this->warn("Marking backups as deleted");
                foreach ($this->deletedBackups as $deletedBackup) {
                    $deletedBackup->delete();
                }
            }

            $this->displayStatistics();

        } catch (Exception $e) {
            $this->error("Failed to restore DB");
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Determines the latest backup thats valid, and returns the path
     *
     * @return void
     */
    protected function getBackupPath()
    {
        $this->info("Getting backup information");
        $path = $this->getBackupPathByFolder('storage/backups/');

        return $path;
    }

    /**
     * Determines backup file in specified folder
     */
    protected function getBackupPathByFolder($rootFolderPath)
    {
        $path = null;

        $files = array_diff(scandir($rootFolderPath), array('.', '..'));

        $keys = array_keys($files);
        $maxKey = $keys[sizeof($keys) - 1];

        // Sorted by name, descending. Return last file
        $path = $rootFolderPath . $files[$maxKey];

        return $path;
    }

    /**
     * DEPRECATED
     *
     * @return void
     */
    protected function getBackupPathByDB()
    {
        $path = null;

        do {

            $latestBackupId = Backup::where('deleted_at', null)
                ->max('id');
            $latestBackup = Backup::where('id', $latestBackupId)->first();

            // Get path from DB
            $this->info("Determining path from latest backup");
            if ($latestBackup == null) {
                throw new Exception("No backups could be found!");
            }

            $path = 'storage/' . $latestBackup->file_path;

            $this->info("Verifying backup file exists");
            if (!File::exists($path)) {
                $this->deletedBackups[] = $latestBackup;
                $latestBackup->delete();
                $this->warn("$path does not exist. Marking as deleted, and trying again");
                $path = null;
            }
        } while (!isset($path));

        $this->info("Valid backup found: $path");

        return $path;
    }

    protected function restoreDB($path)
    {

        $this->info("Getting environment information");


        $isDevelopmentEnv = config("app.env") == 'development';
        $confirmRestore = false;

        $dbConnection = config('database.default');
        $dbUsername = config("database.connections.$dbConnection.username");
        $dbPassword = config("database.connections.$dbConnection.password");
        $dbHost = config("database.connections.$dbConnection.host");
        $dbName = config("database.connections.$dbConnection.database");

        $this->info("dbUsername:$dbUsername");

        if (!$isDevelopmentEnv) {
            $confirmRestore = $this->confirm("You are about to reset $dbName. Are you sure?");
        }

        $command = "mysql -u $dbUsername --password=$dbPassword --host=$dbHost $dbName < $path;";

        $returnVar = null;
        $output = null;

        if ($isDevelopmentEnv || $confirmRestore) {
            $this->info("Restoring database with $path");
            DB::beginTransaction();
            exec($command, $output, $returnVar);
            DB::commit();
        } else {
            $this->error("Did not restore DB. Environment is not a development environment and restoration was not confirmed");
        }
    }

    protected function displayStatistics()
    {
        try {
            $userCount = User::get()->count();
            $hoCount = HiringOrganization::get()->count();
            $contractorCount = Contractor::get()->count();

            $headers = ['property', 'count'];

            $this->table(
                $headers,
                [
                    [
                        'property' => 'Users',
                        'count' => $userCount,
                    ],
                    [
                        'property' => 'Hiring Organizations',
                        'count' => $hoCount,
                    ],
                    [
                        'property' => 'Contractors',
                        'count' => $contractorCount,
                    ],
                ]
            );
        } catch (Exception $e) {
            $this->error("Failed to display statistics");
        }
    }
}
