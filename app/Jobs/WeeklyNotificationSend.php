<?php

namespace App\Jobs;

use App\Models\Contractor;
use App\Notifications\Requirement\WeeklyDigest;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Log;

class WeeklyNotificationSend implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use NotificationTrait;

    public $timeout = 180;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $chunkSize = 50;
        Log::info('Running WeeklyNotificationSend.php');

        $contractorsWithExpiredRequirements = DB::table('contractors')
            ->leftJoin('roles', 'contractors.owner_id', '=', 'roles.id')
            ->join('view_contractor_requirements as vcr', 'vcr.contractor_id', '=', 'contractors.id')
            ->where('requirement_status', 'past_due')
            ->select(
            	'vcr.contractor_id',
                'contractors.name'
            )
            ->groupBy('vcr.contractor_id')
            ->orderBy('vcr.contractor_id')
            ->get();

        foreach ($contractorsWithExpiredRequirements as $index=>$contractor) {
        	Log::info("Processing " . $index . " / " . sizeof($contractorsWithExpiredRequirements));
            try {
                $this->sendEmailForContractor(Contractor::find($contractor->contractor_id));
            } catch (Exception $e) {
                Log::debug("Error for $contractor->contractor_id",[
                	'message' => $e->getMessage()
                ]);
            }
        }
	}

    public function sendEmailForContractor(Contractor $contractor)
    {
        if (isset($contractor, $contractor->id)) {
        	// Making sure contractor has an active subscription
        	if(!$this->checkContractorSubscriptionStatus($contractor)){
                throw new Exception("Contractor $contractor->name does not have a valid subscription.");
        	}

            $pastDue = DB::table('view_contractor_requirements')
                ->where('requirement_status', 'past_due')
                ->where('contractor_id', $contractor->id)
                ->get();

            if (sizeof($pastDue) > 0) {
                $userToNotify = $contractor->owner->user;

                $isValidEmail = $this->isUserEmailValid($userToNotify);

                if ($isValidEmail) {
                    $notification = new WeeklyDigest($contractor, $pastDue);
	                $userToNotify->notify($notification);
                }
            }
        } else {
            throw new Exception('Contractor / Contractor ID was not defined');
        }
    }
}
