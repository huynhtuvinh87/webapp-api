<?php

namespace App\Notifications\Registration;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Traits\NotificationTrait;

class Welcome extends Notification implements ShouldQueue
{
    use Queueable;
    use NotificationTrait;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->queue = 'high';
        $this->connection = 'database';
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
        // validating email
        $this->isUserEmailValid($notifiable);

        $data = [
            'user' => $notifiable,
            'subject' => 'Re: Welcome to Contractor Compliance!'
        ];

        $mailMessage = (new MailMessage)
            ->subject($data['subject'])
            ->view('emails.registration.welcome', $data);

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
