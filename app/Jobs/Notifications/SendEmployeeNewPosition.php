<?php

namespace App\Jobs\Notifications;

use App\Notifications\Relation\NewEmployeePosition;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendEmployeeNewPosition implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationTrait;

    private $user;
    private $position;

    /**
     * Create a new job instance.
     *
     * @param $user
     * @param $position
     */
    public function __construct($user, $position)
    {
        $this->queue = 'low';
        $this->connection = 'database';

        $this->user = $user;
        $this->position = $position;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        // getting role for user
        $role = $this->user->roles()->first();

        if(!isset($role)){
            throw new Exception('Role not found for $this->user->first_name');
        }

        // finding employer (contractor) for role
        $contractor = $role->company()->first();
        if(!isset($contractor)){
            throw new Exception('Contractor (Employer) not found for $this->user->first_name!');
        }

        if(!isset($this->position)){
            throw new Exception("Position was not found!");
        }

        if(!$this->isUserEmailValid($this->user)){
            throw new Exception('Email $this->user->email is not valid, black listing it.');
        }

        $this->user->notify(new NewEmployeePosition($this->position, $contractor));
    }
}
