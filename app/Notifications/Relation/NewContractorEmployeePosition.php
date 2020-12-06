<?php

namespace App\Notifications\Relation;

use App\Models\HiringOrganization;
use App\Models\Position;
use App\Traits\NotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Log;

/**
 * Notification for the contractor owner indicating they have a new assigned
 */
class NewContractorEmployeePosition extends Notification implements ShouldQueue
{
    use Queueable, NotificationTrait;

    private $position;
    private $hiring_organization;

    /**
     * Create a new notification instance.
     *
     * @param Position $position
     * @param HiringOrganization $hiring_organization
     */
    public function __construct(Position $position, HiringOrganization $hiring_organization)
    {
        $this->queue = 'high';
        $this->connection = 'database';
        $this->position = $position;
        $this->hiring_organization = $hiring_organization;
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
        $data = [
            'name' => $notifiable,
            'subject' => "Re: New Employee Position Assigned to You!",
            'hiring_organization_name' => $this->hiring_organization->name,
            'position' => $this->position,
        ];

        $mailMessage = (new MailMessage)
            ->subject($data['subject'])
            ->view('emails.relation.new-contractor-employee-position', $data);

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
