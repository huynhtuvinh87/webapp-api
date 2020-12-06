<?php

namespace App\Notifications\Requirement;

use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Requirement;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Traits\NotificationTrait;
Use Log;

class Declined extends Notification implements ShouldQueue
{
    use Queueable;
    use NotificationTrait;

    private $contractor;
    private $requirement;
    private $reason;

    /**
     * Create a new notification instance.
     *
     * @param Contractor $contractor
     * @param Requirement $requirement
     * @param $reason
     */
    public function __construct(Contractor $contractor, Requirement $requirement, $reason)
    {

        $this->queue = 'high';
        $this->connection = 'database';

        $this->contractor = $contractor;
        $this->requirement = $requirement;
        $this->reason = $reason;

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
        if (!isset($this->contractor)) {
            throw new Exception("Contractor was not defined");
        }

        if (!isset($this->requirement)) {
            throw new Exception("Requirement was not set");
        }

        $company = $this->requirement->hiringOrganization;

        $subjectLine = "Re: Declined Requirement";
        if (isset($this->contractor->owner->user->first_name) && trim($this->contractor->owner->user->first_name) != '') {
            $subjectLine .= " for " . ucwords($this->contractor->owner->user->first_name);
        }
        $data = [
            'company' => $company,
            'contractor' => $this->contractor,
            'requirement' => $this->requirement,
            'reason' => $this->reason ?? null,
        ];

        $mailMessage = (new MailMessage)
            ->subject($subjectLine)
            ->view('emails.requirement.declined', $data);

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
