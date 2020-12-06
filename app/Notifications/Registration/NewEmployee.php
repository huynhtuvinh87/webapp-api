<?php

namespace App\Notifications\Registration;

use App\Models\Contractor;
use App\Models\User;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Log;
use Carbon\Carbon;

class NewEmployee extends VerifyEmail implements ShouldQueue
{
    use Queueable, NotificationTrait;

    private $user;
    private $contractor_id;
    private $inviter;

    /**
     * NewEmployee constructor.
     * @param User $inviter
     * @return void
     */
    public function __construct(User $inviter)
    {
        $this->queue = 'high';
        $this->connection = 'database';
        $this->contractor_id = $inviter->role->entity_id;
        $this->inviter = $inviter;
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
     * @throws Exception
     */
    public function toMail($notifiable)
    {
        try {

            $contractor = Contractor::find($this->contractor_id);
            if (!isset($contractor)) {
                throw new Exception("Contractor not found.");
            }

            if (!isset($this->inviter)) {
                throw new Exception("Inviter not found.");
            }

            $data = [
                'user' => $notifiable,
                'contractor' => $contractor,
                'subject' => 'Re: You Have Been Added to ' . ucwords($contractor->name),
                'inviter' => $this->inviter,
                'verifyEmailLink' => $this->verificationUrl($notifiable),
            ];

            $mailMessage = (new MailMessage)
                ->subject($data['subject'])
                ->view('emails.registration.new-employee', $data);

            $mailMessage = (config('api.bcc_email')) ? $mailMessage->bcc([config('api.bcc_email')]) : $mailMessage;

            $this->logNotification($notifiable, $data);
            return $mailMessage;

        } catch (Exception $exception) {
            Log::error($exception);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    protected function verificationUrl($notifiable)
    {

        // NOTE: This is setting base URL to be 8080 by default
        // Adding the line below since it should be 8000 - verification.verify is on server
        URL::forceRootUrl(config('api.url'));
        $signedRoute = URL::signedRoute(
            'verification.email',
            ['id' => $notifiable->getKey()]
        );


        return $signedRoute;

    }
}
