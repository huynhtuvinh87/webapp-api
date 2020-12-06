<?php

namespace App\Notifications\Requirement;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Log;
use App\Traits\NotificationTrait;

class InWarning extends Notification implements ShouldQueue
{
    use Queueable;
    use NotificationTrait;

    public $user;
    public $requirements;
    public $subject;

    /**
     * Create a new notification instance.
     *
     * @return void
     * @throws Exception
     */
    public function __construct($user, $requirements)
    {

        $this->queue = 'high';
        $this->connection = 'database';

        $this->user = $user;
        if (!isset($this->user)) {
            throw new Exception("User was not defined");
        }

        $this->requirements = $requirements;
        if (!isset($this->requirements)) {
            throw new Exception("Requirements were not set");
        }

        $this->subject = "Re: Mandatory Requirements Set to Expire";
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
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @throws Exception
     */
    public function toMail($notifiable)
    {
        if (!isset($this->user)) {
            throw new Exception("User was not defined");
        }
        if (!isset($this->requirements)) {
            throw new Exception("Requirements were not set");
        }
        if (!isset($this->subject)) {
            throw new Exception("Email subject was not set!");
        }

        $data = [
            'user' => $notifiable,
            'requirements' => $this->requirements
        ];

        $mailMessage = (new MailMessage)
            ->subject($this->subject)
            ->view('emails.requirement.warning', $data);

        $mailMessage = (config('api.bcc_email')) ? $mailMessage->bcc([config('api.bcc_email')]) : $mailMessage ;

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
