<?php

namespace App\Notifications\Integration;

use App\Traits\NotificationTrait;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Log;

class TractionGuestNotCompliantHost extends Notification implements ShouldQueue
{
    use Queueable, NotificationTrait;

    private $hiring_organization;
    private $hiring_organization_user;
    private $contractor;
    private $contractor_user;
    private $corporate_requirements;
    private $employee_requirements;

    /**
     * TractionGuestNotCompliantHost constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->queue = 'high';
        $this->connection = 'database';

        $this->hiring_organization = $data['hiring_organization'];
        $this->hiring_organization_user = $data['hiring_organization_user'];
        $this->contractor = $data['contractor'];
        $this->contractor_user = $data['contractor_user'];
        $this->corporate_requirements = $data['corporate_requirements'];
        $this->employee_requirements = $data['employee_requirements'];
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

    public function toMail($notifiable)
    {
        // validate email
        $this->isUserEmailValid($notifiable);

        $log_data = [
            'hiring_organization' => $this->hiring_organization,
            'hiring_organization_user' => $this->hiring_organization_user,
            'contractor_user' => $this->contractor_user,
            'contractor' => $this->contractor,
            'subject' => 'Re: Contractor Not Compliant',
        ];

        $requirement_data = [
            'corporate_requirements' => $this->corporate_requirements,
            'employee_requirements' => $this->employee_requirements,
        ];

        $data = array_merge($log_data, $requirement_data);

        $this->logNotification($notifiable, $log_data);
        return (new MailMessage)
            ->bcc(['dmachado+host@contractorcompliance.io', (config('api.bcc_email')) ? config('api.bcc_email') : ""])
            ->subject($data['subject'])
            ->view('emails.integration.traction-guest-host', $data);
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
