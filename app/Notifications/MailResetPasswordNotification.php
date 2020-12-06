<?php

namespace App\Notifications;

use App\Traits\NotificationTrait;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MailResetPasswordNotification extends ResetPassword
{
    use Queueable, NotificationTrait;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $data = [
            "subject" => 'Re: Reset Your Contractor Compliance Password',
            "token" => $this->token
        ];

        $mailMessage = (new MailMessage)
            ->subject($data['subject'])
            ->view('emails.auth.reset', $data);

        $mailMessage = ((config('api.bcc_email'))) ? $mailMessage->bcc([config('api.bcc_email')]) : $mailMessage ;

        $this->logNotification($notifiable, $data);
        return $mailMessage;
    }
}
