<?php

namespace App\Notifications\Registration;

use App\Models\HiringOrganization;
use App\Traits\NotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteContractorOwner extends Notification
{
    use Queueable, NotificationTrait;

    private $hiring_organization;
    private $coupon;
    private $token;

    /**
     * Create a new notification instance.
     *
     * @param HiringOrganization $hiring_organization
     * @param $coupon
     * @param $token
     */
    public function __construct(HiringOrganization $hiring_organization, $coupon, $token)
    {
        $this->queue = 'high';
        $this->connection = 'database';

        $this->hiring_organization = $hiring_organization;
        $this->coupon = $coupon;

        $this->url = config('client.web_ui') . "contractor-signup/?token=" . $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
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
     */
    public function toMail($notifiable)
    {

        $data = [
            'name' => $notifiable,
            'subject' => "Re: ALC Schools - Registration Required",
            'hiring_organization' => $this->hiring_organization,
            'coupon' => $this->coupon,
            'registrationURL' => $this->url,
        ];

        $mailMessage = (new MailMessage)
            ->subject($data['subject'])
            ->attach(storage_path('How to Register - Contractor Compliance.pdf'))
            ->view('emails.registration.invite-contractor', $data);

        $mailMessage = (config('api.bcc_email')) ? $mailMessage->bcc([config('api.bcc_email')]) : $mailMessage;

        $this->logNotification($notifiable, $data);
        return $mailMessage;
    }

}
