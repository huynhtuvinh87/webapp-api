<?php

namespace App\Jobs;

use App\Jobs\Notifications\SendEmployeePastDue;
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
 * QueueEmployeePastDue.
 *
 * This script finds all the contractors to be notified,
 * then calls SendContractorPastDue for each contractor
 */
class QueueEmployeePastDue implements ShouldQueue
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
        $this->connection = 'database';
        $this->logStack[] = config('app.env') !== 'development' ? ['slack'] : null;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Getting users with requirement expiring today.
        $userIdsWithPastDueRequirements = DB::table('view_employee_requirements')
            ->join('roles', 'roles.user_id', '=', 'view_employee_requirements.user_id')
            ->join('users', 'users.id', '=', 'view_employee_requirements.user_id')
            ->select('view_employee_requirements.user_id')
            ->where(function ($query) {
                $query->where('exclusion_status', '!=', 'approved')
                    ->orWhereNull('exclusion_status');
            })
            ->where('email_verified_at', '>=', config('api.email_validation_date'))
            ->where('requirement_type', '!=','internal_document')
            ->where('role', '=','employee')
            ->where('due_date', '=', Carbon::now()->toDateString())
            ->whereNotNull('tc_signed_at')
            ->get()
            ->map(function($item){
                return $item->user_id;
            })
            ->toArray();

        $usersToBeNotified = User::whereIn('id', $userIdsWithPastDueRequirements)->get();

        Log::debug("Sending past due employee requirement notification emails to " . count($usersToBeNotified) . " users.");

        if(count($usersToBeNotified) > 0){
            foreach($usersToBeNotified as $user){
                SendEmployeePastDue::dispatch($user);
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
