<?php

namespace App\Notifications\Requirement;

use App\Models\HiringOrganization;
use App\Models\Requirement;
use App\Traits\NotificationTrait;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Log;

class ExclusionApproved extends Notification implements ShouldQueue
{
    use Queueable, NotificationTrait;

    private $requirement;
    private $reason;
    private $hiring_organization_id;

    /**
     * Create a new notification instance.
     *
     * @param Requirement $requirement
     * @param $reason
     * @param $hiring_organization_id
     */
    public function __construct(Requirement $requirement, $reason, $hiring_organization_id)
    {
        $this->queue = 'high';
        $this->connection = 'database';

        $this->requirement = $requirement;
        $this->reason = $reason ?? null;
        $this->hiring_organization_id = $hiring_organization_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @throws Exception
     */
    public function toMail($notifiable)
    {
        // validate email
        $this->isUserEmailValid($notifiable);

        $hiring_organization = HiringOrganization::find($this->hiring_organization_id);
        if (!isset($hiring_organization)) {
            throw new Exception("Could not find Hiring Organization");
        }

        if (!isset($this->requirement)) {
            throw new Exception("Requirement was not set");
        }

        //loading requirement content
        $this->requirement->loadContent();

        $data = [
            'user' => $notifiable,
            'subject' => 'Re: Exclusion Request Approved',
            'hiring_organization' => $hiring_organization,
            'requirement' => $this->requirement,
            'reason' => $this->reason,
        ];

        $mailMessage = (new MailMessage)
            ->subject($data['subject'])
            ->view('emails.requirement.exclusion-approved', $data);

        $mailMessage = ((config('api.bcc_email'))) ? $mailMessage->bcc([config('api.bcc_email')]) : $mailMessage ;

        $this->logNotification($notifiable, $data);
        return $mailMessage;

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
