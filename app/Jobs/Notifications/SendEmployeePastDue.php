<?php

namespace App\Jobs\Notifications;

use App\Models\User;
use App\Notifications\Requirement\PastDue;
use App\Traits\NotificationTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Log;

/**
 * CheckExpiredRequirements
 *
 * This job goes through all users and checks to see if they have expired
 * If any requirements are expired, sends PastDue notification to user
 */
class SendEmployeePastDue implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationTrait;

	private $today;
	private $user;
	private $contractor;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
	public function __construct(User $user)
	{
        $this->queue = 'low';
        $this->connection = 'database';
        $this->today = Carbon::now()->toDateString();

        $this->user = $user;
        $this->contractor = $this->user->roles[0]->company;
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
        Redis::throttle($key)->allow(10000)->every(60)
            ->then(function () {
                try {
                    if (!isset($this->user)) {
                        throw new Exception("User was not defined");
                    }

                    // Validate contractor subscription to avoid send emails to invalid contractors

                    if (!$this->checkContractorSubscriptionStatus($this->contractor)) {
                        throw new Exception("Can't send email to " . $this->user->name . ", his employeer " . $this->contractor->name . " has no active subscription");
                    }

                    // Get list of requirements
                    $requirements = DB::table('view_employee_requirements')
                        ->where('due_date', $this->today)
                        ->where('user_id', $this->user->id)
                        ->where('requirement_type', '!=', 'internal_document')
                        ->get();

                    $hasRequirements = sizeof($requirements) > 0;
                    $hasVerifiedEmail = $this->isUserEmailValid($this->user);

                    $notify_me = $this->user;
                    if ($hasRequirements && $hasVerifiedEmail) {
                        $notify_me->notify(new PastDue(
                            $this->user,
                            $requirements
                        ));
                    } else {
                        Log::info("Not sending email to " . $this->user->email, [
                            'Verified Email' => $hasVerifiedEmail,
                            'Has Requirements' => $hasRequirements
                        ]);
                    }
                } catch (Exception $e) {
                    Log::error("Can't send email to " . $this->user->email);
                }
            }, function () {
                return $this->release(5000);
            });

    }

    public function failed(Exception $error)
    {
        // Send user notification of failure, etc...
		Log::error($error->getMessage());
    }
}
