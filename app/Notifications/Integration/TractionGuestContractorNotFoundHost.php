<?php

namespace App\Notifications\Integration;

use App\Traits\NotificationTrait;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Log;

class TractionGuestContractorNotFoundHost extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels, NotificationTrait;

    protected $hiring_organization;
    protected $hiring_organization_user;
    protected $contractor;
    protected $contractor_user;
    protected $corporate_requirements;
    protected $employee_requirements;

    /**
     * TractionGuestNotCompliantHost constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->queue = 'high';
        $this->connection = 'database';

        $this->hiring_organization = $data['hiring_organization'] ?? null;
        $this->hiring_organization_user = $data['hiring_organization_user'] ?? null;
        $this->contractor = $data['contractor'] ?? null;
        $this->contractor_user = $data['contractor_user'] ?? null;
        $this->corporate_requirements = $data['corporate_requirements'] ?? null;
        $this->employee_requirements = $data['employee_requirements'] ?? null;
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
            'subject' => 'Re: Non-Registered Contractor Attempted Check-In',
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
            ->view('emails.integration.traction-guest-not-system-host', $data);
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
