<?php

namespace App\Jobs;

use App\Models\Contractor;
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
 * QueueWeeklyNotification
 *
 */
class QueueContractorWeeklyNotification implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	use NotificationTrait;

    // public $queue = 'low';

	private $contractorIds = [];
	private $contractorsToBeNotified = null;
	private $logStack = ['daily'];
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->queue = 'low';
		$this->logStack[] = config('app.env') != 'development' ? ['slack'] : null;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
        // NOTE: the code below will prevent pending requirements to be include in the list of PAST DUE
        // based on the fact the Contractor will receive the requirement but he/she already actioned it
        // but the HO may have not approved/declined it yet. Contractor cannot do anything other than wait for the HO
        /*
        ->where(function ($query) {
            $query
                ->where('due_date', '<', Carbon::now()->toDateString())
                ->orWhereNull('completion_date');
        })
        */
        
        // Get contractors with past due requirements
		$this->contractorIds = DB::table('view_contractor_requirements')
			// Selecting just contractor IDs
			->select('contractor_id')
            ->where('requirement_type', '!=', 'internal_document')
			->whereIn('requirement_status', ['past_due'])
            ->where(function ($query) {
                $query->where('exclusion_status', '!=', 'approved')
                    ->orWhereNull('exclusion_status');
            })
            // NOTE: the code below will prevent pending requirements to be include in the list of PAST DUE
            // based on the fact the Contractor will receive the requirement but he/she already actioned it
            // but the HO may have not approved/declined it yet. Contractor cannot do anything other than wait for the HO
            ->where(function ($query) {
                $query
                    ->where('due_date', '<', Carbon::now()->toDateString())
                    ->orWhereNull('completion_date');
            })
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
		if($contractorCount > 0){
			Log::stack($this->logStack)
				->info("Handling " . get_class($this), [
		            "Environment" => env('APP_NAME'),
					'Contractor Count' => $contractorCount,
				]);

			foreach($this->contractorsToBeNotified as $contractor){
				SendWeeklyContractorNotification::dispatch($contractor);
			}
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
