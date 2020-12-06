<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class SystemNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user_id;
    public $message;
    public $action;
    public $action_text;
    public $sender_id;

    /**
     * SystemNotification constructor.
     * @param string $message
     * @param int $user_id recipient
     * @param int|null $sender_id user sending message
     * @param string|null $action <a> href value
     * @param string|null $action_text <a> text
     * @param string|null $secondary_action <a> href value
     * @param string|null $secondary_action_text <a> text
     */
    public function __construct(
        string $message,
        int $user_id,
        int $sender_id = null,
        string $action = null,
        string $action_text = null
    ) {

        $this->user_id = $user_id;
        $this->sender_id = $sender_id;
        $this->message = $message;
        $this->action = $action;
        $this->action_text = $action_text;

    }

    /**
     * Save notification to database
     */
    public function save() : \App\Models\SystemNotification
    {
        return \App\Models\SystemNotification::create([
            'message' => $this->message,
            'action' => $this->action,
            'action_text' => $this->action_text,
            'user_id' => $this->user_id,
            'sender_id' => $this->sender_id
        ]);
    }

    public function send($notification) : void
    {
        $notification->user->notify(new \App\Notifications\SystemNotification($notification));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() : void
    {
        $notification = $this->save();

        $this->send($notification);

    }
}
