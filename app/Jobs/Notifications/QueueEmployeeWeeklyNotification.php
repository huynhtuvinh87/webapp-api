<?php

namespace App\Jobs;

use App\Jobs\Notifications\SendWeeklyEmployeeNotification;
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
 * QueueWeeklyNotification
 *
 */
class QueueEmployeeWeeklyNotification implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	use NotificationTrait;

	private $logStack = ['daily'];
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->queue = 'low';
        $this->connection = 'database';
		$this->logStack[] = config('app.env') != 'development' ? ['slack'] : null;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{

        // Getting users with requirement expired.
        $userIdsWithPastDueRequirementsQuery = DB::table('view_employee_requirements')
            ->select('user_id')
            ->whereIn('requirement_status', [DB::raw("'past_due'")])
            ->where('requirement_type', '!=', DB::raw("'internal_document'"))
            ->where(function ($query) {
                $query
                    ->where('exclusion_status', DB::raw("'declined'"))
                    ->orWhereNull('exclusion_status');
            })
        // NOTE: the code below will prevent pending requirements to be include in the list of PAST DUE
        // based on the fact the Contractor/Employee will receive the requirement but he/she already actioned it
        // but the HO may have not approved/declined it yet. Contractor cannot do anything other than wait for the HO
            ->where(function ($query) {
                $query
                    ->where('due_date', '<', DB::raw("'" . Carbon::now()->toDateString() . "'"))
                    ->orWhereNull('completion_date');
            })
            ->groupBy('user_id');

        $userIdsWithPastDueRequirements = $userIdsWithPastDueRequirementsQuery
            ->get()
            ->map(function ($item) {
                return $item->user_id;
            })
            ->toArray();

        $usersToBeNotified = User::whereIn('id', $userIdsWithPastDueRequirements)
            ->where('email_verified_at', '>',  config('api.email_validation_date')) // everyone with email_verified_at before 2000-01-01 is legacy, and we have not confirmed they have a valid email
            ->whereNotNull('tc_signed_at')
            ->whereNotNull('email_verified_at')
            ->get();

        Log::info("Sending past due employee requirement notification emails to " . sizeof($usersToBeNotified) . " users.");

        if(count($usersToBeNotified) > 0){
            foreach($usersToBeNotified as $user){
				SendWeeklyEmployeeNotification::dispatch($user);
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
