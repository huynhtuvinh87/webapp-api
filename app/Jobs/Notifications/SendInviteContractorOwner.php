<?php

namespace App\Jobs\Notifications;

use App\Models\HiringOrganization;
use App\Models\User;
use App\Notifications\Registration\InviteContractorOwner;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendInviteContractorOwner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationTrait;

    private $user;
    private $hiring_organization;
    private $token;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param HiringOrganization $hiring_organization
     * @param $token
     */
    public function __construct(User $user, HiringOrganization $hiring_organization, $token)
    {
        $this->queue = 'low';
        $this->connection = 'database';

        $this->user = $user;
        $this->token = $token;
        $this->hiring_organization = $hiring_organization;

        // Coupon
        if (config('app.env') == 'production') {
            $this->coupon = "ALC-1211PDSS200";
        } else {
            $this->coupon = "TEST-ALC-COUPON-12345";
        }
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        try {
            if (!isset($this->user)) {
                throw new Exception('User was not defined');
            }

            if (!isset($this->hiring_organization)) {
                throw new Exception('Hiring Organization was not defined');
            }

            if ($this->isUserEmailValid($this->user)) {
                $this->user->notify(new InviteContractorOwner($this->hiring_organization, $this->coupon, $this->token));
            }
            else {
                Log::error("User's email is not valid: ".$this->user->email);
            }

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
}
