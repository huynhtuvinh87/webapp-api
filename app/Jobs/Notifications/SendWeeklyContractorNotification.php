<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\Requirement\WeeklyDigest;
use App\Traits\NotificationTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * SendWeeklyContractorNotification.
 *
 * This process takes in a contractor model,
 * gets the requirements that are past due,
 * then queues the notification to be sent
 */
class SendWeeklyContractorNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use NotificationTrait;

    private $contractor = null;

    /**
     * Create a new job instance.
     *
     * @param mixed $contractor
     *
     * @return void
     */
    public function __construct($contractor)
    {
        $this->queue = 'low';
        $this->connection = 'database';

        if (!isset($contractor)) {
            throw new Exception('Contractor was not passed into sender');
        }

        $this->contractor = $contractor;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $key = get_class($this);

        // TODO: Determine proper rate limit
        if (!isset($this->contractor)) {
            throw new Exception('Contractor was not defined');
        }

        if($this->checkContractorSubscriptionStatus($this->contractor)){

	        // Getting owner from contractor
	        $ownerRole = $this->contractor->owner;
	        if (!isset($ownerRole)) {
	            throw new Exception("Owner role was not defined for $this->contractor->name !");
	        }

	        $ownerUser = $ownerRole->user;

	        if ($this->isUserEmailValid($ownerUser)) {
	            Redis::throttle($key)->allow(2000)->every(60)
	                ->then(function () use ($ownerUser) {
	                    // Get list of requirements
	                    $requirementsQuery = DB::table('view_contractor_requirements')
                            ->select('requirement_name', 'hiring_organization_name')
	                        ->where('contractor_id', $this->contractor->id);

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

	                    $pastDueRequirements = $requirementsQuery
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
                            ->groupBy('requirement_name', 'hiring_organization_name')
	                        ->get();

	                    if (sizeof($pastDueRequirements) > 0) {
	                        $ownerUser->notify(new WeeklyDigest(
		                        $pastDueRequirements
		                    ));
	                    }
	                }, function () {
	                    return $this->release(10);
	                });
	        }
	    } else {
	    	Log::debug("Contractor: " . $this->contractor->name . "");
	    	Log::warn("Contractor has no active subscription - Skipping in " . get_class($this));
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
