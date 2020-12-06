<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Removes old records from tables
 */
class PruneLogTables implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logTables = null;
    protected $pruneDate = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Delete anything before 3 months ago
        $this->pruneDate = Carbon::now()->subMonths(1);
        $this->logTables = collect([
            'request_logs',
            'notification_logs',
            'backups',
        ]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->logTables
            ->each(function ($table) {
                $query = DB::table($table)
                    ->where("updated_at", "<", DB::raw("'{$this->pruneDate}'"));
                $query->delete();
            });

        DB::table("failed_jobs")
            ->where("failed_at", "<", DB::raw("'{$this->pruneDate}'"))
            ->delete();
    }
}
