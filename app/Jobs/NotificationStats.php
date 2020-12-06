<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Log;
use Exception;

/**
 * NotificationStats.
 *
 * This job sends an update to logs / slack with statistics on notifications that had been sent out for the day
 */
class NotificationStats implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $today = null;
    private $title = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // If on a production environment, send info to slack as well
        $this->today = Carbon::now();
        $this->title = "Notifications sent on " . $this->today->toDateString() . " (as of " . $this->today . ")";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Getting notification statistics
        $statsArray = DB::table('notification_logs')
            ->select([
                'notification',
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy('notification')
            ->whereDate('created_at', $this->today->toDateString())
            ->get()
            ->map(function($stat){
                return [
                    'notification' => $stat->notification,
                    'count' => $stat->count
                ];
            })
            ->toArray();

        $statistics = [];
        foreach($statsArray as $stats){
            $statistics[$stats['notification']] = $stats['count'];
        }

        // Logging information to log file
        $this->dailyLogStats($statistics);
        $this->dailySlackStats($statistics);
    }

    public function dailyLogStats($statistics){
        Log::info($this->title);
        foreach ($statistics as $key => $count) {
            Log::info($key . ': ' . $count);
        }
    }

    public function dailySlackStats($statistics){
        if(config('app.env') !== 'production'){
            Log::error("Don't send messages to slack if on development");
        } else {
            Log::channel('slack')->info($this->title, $statistics);
        }
    }
}
