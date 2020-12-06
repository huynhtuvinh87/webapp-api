<?php

namespace App\Jobs\Notifications;

use App\Models\Role;
use App\Models\SubcontractorSurvey;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueSurvey implements ShouldQueue
{
    //
    //
    // CURRENTLY NOT BEING USED, BUT IT WILL IN THE FUTURE
    // THIS IS A JOB TO REINFORCE THE SUBCONTRACTORS SURVEY SENDING THEM AN EMAIL
    // BASED ON THE RULES ON THE MODEL `ROLE` METHOD `getShowSubcontractorSurveyAttribute()`
    //
    //

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->queue = 'low';
        $this->connection = 'database';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $survey_start_date = Carbon::parse(config('api.subcontractor_survey.roles_registered_after'));

        $role_ids = DB::table('roles')
//            ->join('subcontractor_surveys', 'subcontractor_surveys.role_id', '=', 'roles.id')
            ->where("roles.entity_key", "=", "contractor")// get contractors
            ->where("roles.role", "!=", "employee'")// get owners and admin
            ->where("roles.created_at", ">", $survey_start_date)// get roles registered after Feb 2020
            ->whereRaw('NOW() > DATE_ADD(roles.created_at, INTERVAL ' . config('api.subcontractor_survey.roles_receive_email_after') . ' DAY)')// emails goes to roles after 37 days
            ->whereNull('roles.deleted_at')
//            ->whereNull('subcontractor_surveys.email_sent_at') // if email reminder hasnt been sent yet
            ->distinct()
            ->limit(15)
            ->pluck('roles.id');

        Log::info(json_encode($role_ids));

        foreach ($role_ids as $id) {
            $role = Role::find($id);
            $survey = $role->subcontractorSurvey;

            if (!empty($survey)) {
                // has filled survey
                continue;
            } else {

                //check if limit of contractors surveyed has been reached
                $contractor_being_surveyed = SubcontractorSurvey::find($role->entity_id);
                $total_contractors_surveyed = SubcontractorSurvey::distinct('entity_id')->count('entity_id');

                if ($contractor_being_surveyed || ($total_contractors_surveyed < config('api.subcontractor_survey.limit_contractors'))) {
                    Log::info("send email");
                }

            }

        }
    }
}
