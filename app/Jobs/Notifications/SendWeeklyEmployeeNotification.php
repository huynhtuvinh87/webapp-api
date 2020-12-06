<?php

namespace App\Jobs\Notifications;

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
 * SendWeeklyEmployeeNotification
 *
 * This process takes in a contractor model,
 * gets the requirements that are past due,
 * then queues the notification to be sent
 */
class SendWeeklyEmployeeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationTrait;

    private $user = null;

    /**
     * Create a new job instance.
     *
     * @throws Exception
     * @return void
     */
    public function __construct($user)
    {
        $this->queue = 'low';
        $this->connection = 'database';

        if (!isset($user)) {
            throw new Exception("User was not passed into sender");
        }

        $this->user = $user;
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
        if (!isset($this->user)) {
            throw new Exception("User was not defined");
        }

        $user = $this->user;

        if ($this->isUserEmailValid($user)) {
            Redis::throttle($key)->allow(2000)->every(60)
                ->then(function () use ($user) {

                    // Get list of requirements
                    $requirementsQuery = DB::table('view_employee_requirements')
                        ->select('requirement_name', 'hiring_organization_name')
                        ->where('user_id', $this->user->id);

                    $pastDueRequirements = $requirementsQuery
                        ->where('requirement_type', '!=', 'internal_document')
                        ->whereIn('requirement_status', ['past_due'])
                        ->where(function ($query) {
                            $query->where('exclusion_status', '=', 'declined')
                                ->orWhereNull('exclusion_status');
                        })
                        // NOTE: the code below will prevent pending requirements to be include in the list of PAST DUE
                        // based on the fact the Contractor/Employee will receive the requirement but he/she already actioned it
                        // but the HO may have not approved/declined it yet. Contractor cannot do anything other than wait for the HO
                        ->where(function ($query) {
                            $query
                                ->where('due_date', '<', Carbon::now()->toDateString())
                                ->orWhereNull('completion_date');
                        })
                        ->groupBy('requirement_name', 'hiring_organization_name')
                        ->get();

                    if (sizeof($pastDueRequirements) > 0) {
                        $user->notify(new WeeklyDigest(
                            $pastDueRequirements
                        ));
                    }
                }, function () {
                    return $this->release(10);
                });
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
