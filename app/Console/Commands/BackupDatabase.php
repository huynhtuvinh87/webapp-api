<?php

namespace App\Console\Commands;

use App\Models;
use Illuminate\Console\Command;
use Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $storageLocation = null;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a db backup and stores it in local storage';

    protected $processes;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $backupFolder = 'backups';
        // NOTE: Time first, then environment: That way the latest file is always used, regardless of environment
        // When calling restore:db, it sorts alphabetically.
        $this->storageLocation = $backupFolder . '/' . date("Y-m-d_H:i") . '-' . config('app.name') . '.sql';
        $s3Location = 's3://contractorcompliance.io/backups/' . config('app.name') . '/' . date("Y-m-d") . '/db/';

        $dbDumpCommand = sprintf(
            // NOTE: Tried using --skip-definer, but it was undefined on local
            // sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/' removes DEFINER lines
            // sed -e 's/SET collation_connection .* utf8mb4_0900_ai_ci//'
            "mysqldump -h %s -P %s -u %s --password=%s --routines=0 --triggers=0 --events=0 %s | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/' | sed -e 's/SET collation_connection .* utf8mb4_0900_ai_ci //' > %s",
            config('database.connections.mysql.host'),
            config('database.connections.mysql.port'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            storage_path($this->storageLocation)
        );

        $this->processes = [
            // Make backup directory
            new Process(
                sprintf(
                    'mkdir -p %s',
                    storage_path($backupFolder),
                )
            ),
            new Process($dbDumpCommand),

        ];

        if (config('app.env') != 'development') {
            // Syncing public folder to backup location
            $this->processes[] = new Process(
                sprintf(
                    'aws s3 sync %s %s',
                    storage_path($backupFolder),
                    $s3Location
                ),
            );
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            foreach ($this->processes as $process) {
                $process->mustRun();
            }

            Models\Backup::create([
                'file_path' => $this->storageLocation,
                'environment' => config('app.name')
            ]);

            if (config('app.env') != 'development') {
                Log::channel("slack")->info("Backup Created", [
                    'File Path' => $this->storageLocation,
                    'Environment' => config('app.name'),
                ]);
            }
            $this->info('The backup has been proceed successfully.');
            $this->info("Backup Location: $this->storageLocation");
        } catch (ProcessFailedException $exception) {
            $this->error('The backup process has failed.');
            $this->error($exception);
        }
    }
}
