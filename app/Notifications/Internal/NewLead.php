<?php

namespace App\Notifications\Internal;

use App\Models\Role;
use App\Traits\NotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class NewLead extends Notification implements ShouldQueue
{
    use Queueable, NotificationTrait;

    private $role;
    private $answer;

    /**
     * Create a new notification instance.
     *
     * @param $role_id
     * @param $answer
     */
    public function __construct($role_id, $answer)
    {
        $this->queue = 'low';
        $this->connection = 'database';

        $this->role = Role::whereId($role_id)->with(['user', 'company'])->first();
        $this->answer = $answer;
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
            'subject' => "Re: New Lead from " . ucwords($this->role->company->name),
            'role' => $this->role,
            'answer' => $this->answer,
        ];

        $mailMessage = (new MailMessage)
            ->subject($data['subject'])
            ->view('emails.internal.new-lead', $data);

        $mailMessage = (config('api.bcc_email')) ? $mailMessage->bcc([config('api.bcc_email')]) : $mailMessage;

        $this->logNotification($notifiable, $data);
        return $mailMessage;
    }

}
