<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class StripeMetadataNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $contractors;
    private $environmentName;
    private $notificationTimeStamp;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($contractorsProcessed)
    {
        $this->contractors = $contractorsProcessed;
        $this->environmentName = env('APP_NAME');
        $this->notificationTimeStamp = date("Y-m-d H:i:s");
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->content("$this->environmentName - $this->notificationTimeStamp: Stripe Metadata has been updated. $this->contractors contractors had no metadata and have been updated.");
    }
}
