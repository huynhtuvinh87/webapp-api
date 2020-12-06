<?php

namespace App\Notifications\Registration;

use App\Models\Role;
use App\Traits\NotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SubcontractorSurveyConfirmation extends Notification
{
    use Queueable, NotificationTrait;

    private $role;
    private $contractor;

    /**
     * Create a new notification instance.
     *
     * @param $role_id
     */
    public function __construct($role_id)
    {
        $this->queue = 'low';
        $this->connection = 'database';

        $this->role = Role::find($role_id);
        $this->contractor = $this->role->company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
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
     */
    public function toMail($notifiable)
    {
        $data = [
            'name' => $notifiable,
            'subject' => "Re: Finishing Your Contractor Compliance Setup",
            'contractor' => $this->contractor,
        ];

        $mailMessage = (new MailMessage)
            ->subject($data['subject'])
            ->view('emails.registration.subcontractor-survey-confirmation', $data);

        $mailMessage = (config('api.bcc_email')) ? $mailMessage->bcc([config('api.bcc_email')]) : $mailMessage;

        $this->logNotification($notifiable, $data);
        return $mailMessage;
    }

}
