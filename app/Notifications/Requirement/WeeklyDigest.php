<?php

namespace App\Notifications\Requirement;

use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Contractor;
use Log;

class WeeklyDigest extends Notification implements ShouldQueue
{
    use Queueable;
    use NotificationTrait;

    private $subject;
    private $pastDue;

    /**
     * Create a new notification instance.
     *
     * @throws Exception
     * @return void
     */
    public function __construct($pastDue)
    {
        $this->queue = 'high';
        $this->connection = 'database';

        $this->pastDue = $pastDue;

        if (!isset($this->pastDue)) {
            throw new Exception('Past due requirements were not set');
        }

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
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
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @throws Exception
     */
    public function toMail($notifiable)
    {
        try {

        	// Adding users name to the end of the subject line
            $subject = 'Re: Contractor Compliance Past Due Tasks Weekly Digest';
	        if (isset($notifiable->first_name) && trim($notifiable->first_name) !== '') {
                $subject .= ' for ' . ucwords($notifiable->first_name);
	        }

            if (sizeof($this->pastDue) > 0) {
                $data = [
                	'user' => $notifiable,
                    'subject' => $subject,
                    'requirements' => $this->pastDue,
                ];

                $mailMessage = (new MailMessage)
                    ->subject($data['subject'])
                    ->view('emails.requirement.weekly-digest', $data);

                $mailMessage = (config('api.bcc_email')) ? $mailMessage->bcc([config('api.bcc_email')]) : $mailMessage ;

                $this->logNotification($notifiable, $data);

                return $mailMessage;

            }
        } catch (Exception $e) {
            Log::error($e);
            throw $e;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
        ];
    }
}
