<?php

namespace App\Notifications\Registration;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Traits\NotificationTrait;

class InviteReminder extends Notification implements ShouldQueue
{
    use Queueable;
    use NotificationTrait;

    private $subject;
    private $contractor;
    private $hiringOrgId;
    private $token;

    /**
     * Create a new notification instance.
     *
     * @param mixed $invitationToken
     *
     * @return void
     */
    public function __construct($contractor, $hiringOrgId, $token)
    {
        $this->contractor = $contractor;
        $this->hiringOrgId = $hiringOrgId;
        $this->token = $token;
        $this->subject = 'Re: Reminder Mandatory Registration for Contractors of ';
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
     */
    public function toMail($notifiable)
    {
        try {
            if(!isset($this->contractor)){
            	throw new Exception("Contractor was not set");
            }
            if(!isset($this->hiringOrgId)){
            	throw new Exception("Hiring Org Id was not set");
            }

            if(!isset($this->subject)){
            	throw new Exception("Subject was not set");
            }

            $emailArgs = [
                'token' => $this->token,
                'orgName' => $this->contractor->name,
                // NOTE: This is used to keep track of which hiring org the invite was sent out for
                'hiring_organization_id' => $this->hiringOrgId,
            ];

            $this->logNotification($notifiable, $emailArgs);
            return (new MailMessage())
                ->subject($this->subject . ucwords($this->contractor->name))
                ->view('emails.registration.invite', $emailArgs);

        } catch (Exception $e) {
            $this->handleError($e);
            return null;
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

    public function handleError(Exception $e)
    {
        Log::debug(get_class($this));
        Log::error($e->getMessage());
    }
}
