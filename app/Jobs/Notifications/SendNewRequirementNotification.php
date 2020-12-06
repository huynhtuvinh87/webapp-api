<?php

namespace App\Jobs\Notifications;

use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\User;
use App\Notifications\Requirement\NewRequirement;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNewRequirementNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use NotificationTrait;

    private $user;
    private $position;
    private $hiringOrganization;

    /**
     * Create a new job instance.
     *
     * @param mixed $user
     * @param mixed $position
     *
     * @return void
     */
    public function __construct(User $user, Position $position)
    {
        $this->queue = 'low';
        $this->connection = 'database';
        $this->user = $user;
        $this->position = $position;
        $this->hiringOrganization = HiringOrganization::find($position->hiring_organization_id);
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
        try {
            if (!isset($this->user)) {
                throw new Exception('User was not defined');
            }
            if (!isset($this->position)) {
                throw new Exception('Position was not defined');
            }
            if (!isset($this->hiringOrganization)) {
                throw new Exception('Hiring Organization was not defined');
            }

            $hiringOrgName = $this->hiringOrganization->name;

            // NOTE: Wanted to use user->notificationLogs, but logs are stored when the notification is processed.
            // At this point, the notification log would not be processed.
            if (!$this->isUserEmailValid($this->user)) {
                throw new Exception('Bad E-Mail');
            }

            $this->user->notify(new NewRequirement($hiringOrgName));

            return true;
        } catch (Exception $e) {
        	Log::error("Failed to send notification to user (id: " . $this->user->id . ")");
            Log::error($e->getMessage(), [
                'user' => $this->user,
                'this class' => get_class($this)
            ]);

            return false;
        }
    }

    public function failed(Exception $e)
    {
        Log::error($e->getMessage());

        return false;
    }
}
