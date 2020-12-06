<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Contractor;
use Log;
use Skmetaly\EmailVerifier\Facades\EmailVerifier;
use App\Notifications\Requirement\InWarning;
use Exception;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use App\Traits\NotificationTrait;

/**
 * SendContractorWarning
 *
 * This process takes in a contractor model,
 * gets the requirements that are in warning,
 * then queues the notification to be sent
 */
class SendContractorWarning implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	use NotificationTrait;

	private $contractor = null;
	private $expiryDate = null;

    /**
     * Create a new job instance.
     *
     * @return void
     * @throws Exception
     */
	public function __construct($contractor)
	{
        $this->queue = 'low';
        $this->connection = 'database';

		if(!isset($contractor)){
			throw new Exception("Contractor was not passed into sender");
		}

		$this->contractor = $contractor;

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
		$key = get_class($this);

		// TODO: Determine proper rate limit
		Redis::throttle($key)->allow(10000)->every(60)
			->then(function () {
				if(!isset($this->contractor)){
					throw new Exception("Contractor was not defined");
				}

				// Contractor subscription validation to avoid send emails to invalid contractors
                if(!($this->checkContractorSubscriptionStatus($this->contractor))){
                    throw new Exception("Contractor $this->contractor->name does not have a valid subscription.");
                }

				// Getting owner from contractor
				$ownerRole = $this->contractor->owner;
				if(!isset($ownerRole)){
					throw new Exception("Owner role was not defined for $this->contractor->name !");
				}

				$ownerUser = $ownerRole->user;

				// Get list of requirements
				$requirements = DB::table('view_contractor_requirements')
                    ->where(function ($query) {
                        $query->where('exclusion_status', '!=', 'approved')
                            ->orWhereNull('exclusion_status');
                    })
                    ->where('requirement_type', '!=', 'internal_document')
					->where('requirement_status', 'in_warning')
					->where('warning_date', $this->expiryDate)
					->where('contractor_id', $this->contractor->id)
					->get();

				$hasRequirements = sizeof($requirements) > 0;
				$hasVerifiedEmail = $this->isUserEmailValid($ownerUser);

				if($hasRequirements && $hasVerifiedEmail){
			        $ownerUser->notify(new InWarning(
			            $ownerUser,
			            $requirements
			        ));
				} else {
					Log::info("Not sending email", [
						'Has Requirements' => $hasRequirements,
						'Verified Email' => $hasVerifiedEmail
					]);
				}
			}, function () {
			    return $this->release(5000);
			});

	}

    public function failed(Exception $exception)
    {
        // Send user notification of failure, etc...
    	Log::error(get_class($this));
		Log::error($exception->getMessage());
		Log::error($exception);
    }
}
