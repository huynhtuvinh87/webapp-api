<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QueryReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $subject = null;
    private $bodyLines = null;
    private $file = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($subject, $file, $bodyLines = null)
    {
        $this->queue = 'low';
        $this->connection = 'database';
        $this->file = $file;
        $this->subject = $subject;

        if ($bodyLines != null) {
            $this->bodyLines = $bodyLines;
        } else {
            $this->bodyLines = [
                "Here is the requested report"
            ];
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
        $fileName = $this->file->name;
        // Live for 72 hours
        $link = $this->file->getFullPath(72 * 60);
        $path = $this->file->path;
        // $timeString = $this->time->toDateString();

        $message = (new MailMessage)
            ->subject($this->subject)
            ->action('View Report', $link);

        // Adding additional body lines
        foreach ($this->bodyLines as $bodyLine) {
            $message->line($bodyLine);
        }

        $message
            ->line("Please note that the link will be active for 72 hours. If you require access to the report again, please ask for `$path`.");

        return $message;
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
