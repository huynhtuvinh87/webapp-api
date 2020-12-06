<?php

namespace App\Jobs\Notifications;

use App\Models\Contractor;
use App\Models\User;
use App\Notifications\Registration\InviteReminder;
use App\Traits\NotificationTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInviteReminder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use NotificationTrait;

    private $subject;
    /** How many notifications can be sent out for each invite (each invite is differentiated by hiring org) */
    private $maxInviteNotificationCount;
    /** Days between notifications */
    private $bufferTimeRange;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->queue = 'low';
        $this->connection = 'database';
        $this->maxInviteNotificationCount = 4;
        $this->bufferTimeRange = 7;
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     *
     * @return void
     */
    public function handle()
    {
        // Contractors to be notified
        $contractors = Contractor::with('invites')
            ->whereHas('invites', function ($query) {
                $query->whereDate(
                    'invited_at',
                    '<',
                    Carbon::now()->subDays($this->bufferTimeRange)->toDateString()
                );
            })
            ->get();

        foreach ($contractors as $contractor) {
            if (!isset($contractor)) {
                throw new Exception('Contractor was not defined');
            }
            foreach ($contractor->invites as $invite) {
                try {
                	// Log::info($invite->pivot);
                    if (!isset($invite)) {
                        throw new Exception('Invite was not set');
                    }
                    // Getting owner from contractor
                    $owner_role = $contractor->owner;
                    if (!isset($owner_role)) {
                        throw new Exception("Owner role was not defined for $contractor->name !");
                    }

                    $owner_user = $owner_role->user;
                    if (!isset($owner_user)) {
                        throw new Exception("Owner user was not found for $contractor->name!");
                    }

                    // If user has not received a notification within the past 7 days, send them a notification (max 4 times)
                    // Get the notifications that have been sent to the user for the given hiring org
                    $inviteNotifications = $owner_user->notificationLogs
	                    ->where('notification', InviteReminder::class)
	                    ->where('data', 'NOT LIKE', '%"hiring_organization_id":"' . $invite->id . '"%')
	                    ->sortByDesc('created_at');

                    // Dont send more notifications after max count
                    if (sizeof($inviteNotifications) < $this->maxInviteNotificationCount) {
                        $latestNotifWithinTimeRange = $inviteNotifications
	                        ->where('created_at', '>', Carbon::now()->subDays($this->bufferTimeRange)->toDateString())
	                        ->first();

                        // If no notifications have been sent within the buffer range, then send invite
                        if (!isset($latestNotifWithinTimeRange)) {
                        	$invitePivot = $invite->pivot;
                            $owner_user->notify(new InviteReminder(
	                            $contractor,
	                            $invite->pivot->hiring_organization_id,
	                            $invite->pivot->invite_code
	                        ));
                        }
                    } else {
                        Log::info('Skipping invitation email - max count exceeded', [
	                        $contractor,
	                        $invites[0]
	                    ]);
                    }
                } catch (Exception $e) {
                    $this->failed($e);
                }
            }
        }
    }

    public function failed(Exception $exception)
    {
        // Send user notification of failure, etc...
        Log::error(get_class($this));
        Log::error($exception->getMessage());
        // Log::error($exception);
    }
}
