<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Log;

class BackupFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload files to S3 Server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $backupFolder = 'backups';
        $s3Location = 's3://contractorcompliance.io/backups/' . config('app.name') . '/files/';

        $this->processes = [
            // Syncing public folder to backup location
            new Process(
                sprintf(
                    'aws s3 sync %s %s',
                    storage_path('app/public'),
                    $s3Location
                ),
            ),
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            foreach($this->processes as $process){
                // Setting timeout for process to be 20 min (1200 seconds) as the tar command can take a while
                // Default timeout is 60 seconds
                $process->setTimeout(900);
                $process->mustRun();
            }

            $this->info('The backup has been proceed successfully.');
            Log::channel("slack")->info(
                "Files have been backed up successfully", [
                "Environment" => env('APP_NAME')
            ]);
        } catch (ProcessFailedException $exception) {
            Log::channel("slack")->error(
                "Files failed to backup", [
                "Environment" => env('APP_NAME'),
                "message"=>$exception->getMessage()
            ]);
            $this->error('The backup process has been failed.');
            $this->error($exception);
        }
    }
}
