<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $note;
    private $sender;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(\App\Models\SystemNotification $systemNotification)
    {
        $this->note = $systemNotification;

        if ($this->note->sender()->count()){
            $this->sender = $this->note->sender;
        }
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
        return (new MailMessage)
            ->subject('Re: New Contractor Compliance System Notification')
            ->replyTo($this->sender ? $this->sender->email : config('mail.from.address'))
            ->view('emails.notification', [
                'note' => $this->note,
                'sender' => $this->sender
            ]);
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
