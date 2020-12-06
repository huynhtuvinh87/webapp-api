<?php

namespace App\Notifications\Registration;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Traits\NotificationTrait;

class Invite extends Notification implements ShouldQueue
{
    use Queueable, NotificationTrait;

    private $orgName;
    private $emailArguments;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token = null, $orgName = null)
    {

        $this->queue = 'high';
        $this->connection = 'database';

        $registration_url = (!is_null($token)) ? config('client.web_ui') . 'contractor-signup/?token=' . $token : config('client.web_ui') . 'contractor-signup/';

        $this->orgName = $orgName;
        $this->emailArguments = [
            'registration_url' => $registration_url,
            'orgName' => $this->orgName
        ];
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
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $this->logNotification($notifiable, $this->emailArguments);
        return (new MailMessage)
            ->bcc([(config('api.bcc_email')) ? config('api.bcc_email') : "", config('api.success_email')])
            ->subject("Re: Mandatory Registration for Contractors of " . ucwords($this->orgName))
            ->view('emails.registration.invite', $this->emailArguments);
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
