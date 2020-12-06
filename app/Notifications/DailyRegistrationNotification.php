<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DailyRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $file = null;
    private $time = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($file, $time)
    {
        $this->queue = 'low';
        $this->connection = 'database';
        $this->file = $file;
        $this->time = $time;
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
        $fileName = $this->file->name;
        // Live for 72 hours
        $link = $this->file->getFullPath(72*60);
        $path = $this->file->path;
        $timeString = $this->time->toDateString();

        return (new MailMessage)
                    ->subject('Re: Daily Registration Report')
                    ->line("Here is your daily registration report of new contractors registering on $timeString til now.")
                    ->action('View Report', $link)
                    ->line("Please note that the link will be active for 72 hours. If you require access to the report again, please ask for `$path`.")
                    ->line("Please cross check the report periodically to ensure there are no errors. If you have any questions, feel free to let me know!");
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
