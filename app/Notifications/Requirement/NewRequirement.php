<?php

namespace App\Notifications\Requirement;

use App\Traits\NotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRequirement extends Notification
{
    use Queueable;
    use NotificationTrait;

    private $user_name;
    private $hiring_organization_name;
    private $autoassigned;
    private $subject;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($hiring_organization_name, $autoassigned = false)
    {
        $this->queue = 'high';
        $this->connection = 'database';

        $this->hiring_organization_name = $hiring_organization_name;
        $this->autoassigned = $autoassigned;
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
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @throws \Exception
     */
    public function toMail($notifiable)
    {
    	if($this->isUserEmailValid($notifiable)){
    	    $data = [
                'subject' => 'Re: ' . ucwords($this->hiring_organization_name) . ' Has Assigned New Requirements to ' . ucwords($notifiable->first_name),
                'name' => $notifiable->first_name,
                'position_autoassigned' => $this->autoassigned,
                'hiring_organization_name' => $this->hiring_organization_name,
            ];

            $mailMessage = (new MailMessage)
                ->subject($data['subject'])
                ->view('emails.requirement.new', $data);

            $mailMessage = (config('api.bcc_email')) ? $mailMessage->bcc([config('api.bcc_email')]) : $mailMessage ;

            $this->logNotification($notifiable, $data);
	        return $mailMessage;

    	}
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
