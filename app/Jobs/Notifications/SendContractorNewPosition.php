<?php

namespace App\Jobs\Notifications;

use App\Notifications\Relation\NewContractorCorporatePosition;
use App\Notifications\Relation\NewContractorEmployeePosition;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendContractorNewPosition implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationTrait;

    private $contractor;
    private $position;

    /**
     * Create a new job instance.
     *
     * @param $contractor
     * @param $position
     */
    public function __construct($contractor, $position)
    {
        $this->queue = 'low';
        $this->connection = 'database';
        $this->contractor = $contractor;
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
        // Validation for contractor
        if(!$this->checkContractorSubscriptionStatus($this->contractor)){
            throw new Exception("Contractor does not have a valid subscription");
        }

        // Getting owner from contractor
        $ownerRole = $this->contractor->owner;
        if(!isset($ownerRole)){
            throw new Exception("Owner role was not defined!");
        }

        $hiring_organization = $this->contractor->hiringOrganizations()->first();
        $ownerUser = $ownerRole->user;

        if(!isset($this->position)){
            throw new Exception("Position was not found!");
        }

        if(!$this->isUserEmailValid($ownerUser)){
            throw new Exception("Users email is not valid.");
        }

        if($this->position->position_type == 'contractor'){
            $ownerUser->notify(new NewContractorCorporatePosition($this->position, $hiring_organization));
        } else {
            $ownerUser->notify(new NewContractorEmployeePosition($this->position, $hiring_organization));
        }
    }
}
