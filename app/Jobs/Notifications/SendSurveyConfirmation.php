<?php

namespace App\Jobs\Notifications;

use App\Models\Role;
use App\Notifications\Registration\SubcontractorSurveyConfirmation;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSurveyConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $role;
    private $user;
    private $answer;

    /**
     * Create a new job instance.
     *
     * @param $role_id
     * @param $answer
     */
    public function __construct($role_id)
    {
        $this->queue = 'low';
        $this->connection = 'database';

        $this->role = Role::find($role_id);
        $this->user = $this->role->user;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        if (!isset($this->role)) {
            throw new Exception('Role not found');
        }

        if (!isset($this->user)) {
            throw new Exception('Contractor not found');
        }

        $this->user->notify(new SubcontractorSurveyConfirmation($this->role->id));
    }
}
