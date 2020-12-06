<?php

namespace App\Jobs;

use App\Models\Contractor;
use App\Models\User;
use App\Traits\NotificationTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Log;

/**
 * QueueContractorPastDue.
 *
 * This script finds all the contractors to be notified,
 * then calls SendContractorPastDue for each contractor
 */
class QueueContractorPastDue implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use NotificationTrait;

    private $contractorIds = [];
    private $contractorsToBeNotified = [];
    private $expiryDate = null;
    private $logStack = ['daily'];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->queue = 'low';
        $this->logStack[] = config('app.env') !== 'development' ? ['slack'] : null;

        $this->expiryDate = Carbon::now()
            ->toDateString();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->contractorIds = DB::table('view_contractor_requirements')
            // Selecting just contractor IDs
            ->select('contractor_id')
            ->where('requirement_status', 'past_due')
            ->where('requirement_type', '!=', 'internal_document')
            ->where(function ($query) {
                $query->where('exclusion_status', '!=', 'approved')
                    ->orWhereNull('exclusion_status');
            })
            ->where('due_date', $this->expiryDate)
            ->groupBy('contractor_id')
            ->get();

        // Use whereIn on IDs to get contractors
        // Found to be faster than using map to call Contractor::find on each ID
        $this->contractorsToBeNotified = $this->filterOutInactiveContractors(
                Contractor::whereIn('id', $this->contractorIds
                ->map(function ($c) {
                    return $c->contractor_id;
                })
                ->toArray()
            )
            ->get()
        );

        $contractorCount = sizeof($this->contractorsToBeNotified);
        if ($contractorCount > 0) {
            Log::stack($this->logStack)
                ->info('Sending Expired Requirement emails to Contractors', [
                    'Environment' => env('APP_NAME'),
                    'Contractor Count' => $contractorCount,
                    'Expiary Date' => $this->expiryDate
                ]);

            foreach ($this->contractorsToBeNotified as $contractor) {
                SendContractorPastDue::dispatch($contractor);
            }
        } else {
            Log::stack($this->logStack)
                ->info('No contractors needed to be notified by ' . get_class($this));
        }
    }

    public function failed(Exception $exception)
    {
        // Send user notification of failure, etc...
        Log::error(get_class($this));
        Log::error($exception->getMessage());
        Log::error($exception);
    }
}
