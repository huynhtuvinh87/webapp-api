<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;

class LogRedisStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:redis';

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
        $connection = null;
        $queues = ['high', 'default', 'low'];
        $queueTypes = ['delayed', 'reserved'];
        $logInfo = [];
        $jobs = [];
        $jobLogCountLimit = 10;

        $connection = \Queue::getRedis()->connection($connection);

        foreach ($queues as $queue) {
            foreach ($queueTypes as $type) {
                //For the delayed jobs
                // $count = $connection->zcount('queues:' . $queue . ':' . $type, 0, -1);
                // $logInfo[$queue . ':' . $type] = $count;

                $genericCount = \Redis::llen('queues:' . $queue);
                $logInfo[$queue] = $genericCount;

                $redisJobs = $connection->zrange('queues:' . $queue . ':' . $type, 0, -1);

                foreach ($redisJobs as $job) {
                    $jobs[] = $job;
                }
            }
        }

        Log::info('Queue Counts', $logInfo);

        if (sizeof($jobs) < $jobLogCountLimit) {
            foreach ($jobs as $jobString) {
                $job = json_decode($jobString);
                if (isset($job)) {
                    Log::info('Job', [
                    'job' => $job->job ?? null,
                    'commandName' => $job->data->commandName ?? null,
                    'displayName' => $job->displayName ?? null,
                ]);
                }
            }
        } else {
        	Log::warn("Job count > " . $jobLogCountLimit . ". Not displaying jobs for your sanity");
        }
    }
}
