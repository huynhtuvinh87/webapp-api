<?php

namespace App\Traits;

use App\Models\ExclusionRequest;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Models\RequirementHistoryReview;
use App\Models\Role;
use Carbon\Carbon;
use Exception;
use Log;

trait RequirementStatusTrait
{

    /**
     * NOTE: THis is being overwritten in requirementStatusChip.vue.
     * If the current role is a contractor and the requirement is waiting, replace the warning icon with a thumb
     * DEV-1340
     * DEV-1544
     *
     * @param [type] $requirement_id
     * @param [type] $contractor_id
     * @param [type] $role_id
     * @param [type] $resource_id
     * @return void
     */
    public function getRequirementStatus($requirement_id, $contractor_id=null, $role_id=null, $resource_id=null){
        if($role_id){ // employee requirement
            $role = Role::find($role_id);
            $contractor_id = $role->company->id;
            $is_employee_requirement = true;
            $document_uploaded = $this->hasDocumentUploaded($requirement_id, $contractor_id, $role_id);
        } else { // corporate requirement
            $role = null;
            $is_employee_requirement = false;
            $document_uploaded = $this->hasDocumentUploaded($requirement_id, $contractor_id, null, $resource_id);
        }

        $requirement = Requirement::find($requirement_id);
        $submission = null;

        if( $document_uploaded ){ // document uploaded

            if($is_employee_requirement){ // employee requirement
                $submission = $this->submissionInfo($requirement_id, $contractor_id, $role_id);
                $requirement_review = $this->reviewStatus($requirement->id, $contractor_id, $role);
            } else { // corporate requirement
                $submission = $this->submissionInfo($requirement_id, $contractor_id, null, $resource_id);
                $requirement_review = $this->reviewStatus($requirement->id, $contractor_id, null, $resource_id);
            }

            if ($submission['status'] == 'expired') { // document uploaded | submission expired

                if($this->hasRequestedExclusion($requirement_id, $contractor_id)){ // document uploaded | submission expired | has requested exclusion

                    if($is_employee_requirement){
                        $exclusion_data = $this->exclusionStatus($requirement_id, '', $role_id);
                    } else {
                        $exclusion_data = $this->exclusionStatus($requirement_id, $contractor_id);
                    }
                    $icon = $exclusion_data['thumb'];
                    $description = $exclusion_data['description'];
                    $requirement_status = $exclusion_data['status'];

                } else { // document uploaded | submission expired | has NOT requested exclusion
                    $icon = $this->expiredThumb();
                    $description = "Expired";
                    $requirement_status = $this->expiredStatus();
                }

            } else { // document uploaded | submission on_time OR in_warning

                if($this->autoApproved($requirement_id)){ // document uploaded | submission on_time OR in_warning | auto-approved

                    if ($requirement_review['status'] == 'declined' && $requirement_review['review_date'] > $submission['completion_date']){ // document uploaded | submission on_time OR in_warning | auto-approved | status declined
                        $icon = $this->whiteThumbDown();
                        $description = "Declined";
                        $requirement_status = $this->declinedStatus();
                    } else {
                        //Is it in warning? Mark it as pending
                        //bugfix/DEV-1790
                        /*
                        if($current_status == 'in_warning') {
                            $icon = $this->warningThumb();
                            $description = "Pending Review";
                            $requirement_status = 'pending';
                        }
                        else {
                            */
                            $icon = $this->whiteThumbUp();
                            $description = "Auto-Approved";
                            $requirement_status = $this->completedStatus();
                        }
                       
//                    }

                } else { // document uploaded | submission on_time OR in_warning | NOT auto-approved
                    if ($requirement_review['status'] == 'approved'){ // document uploaded | submission on_time OR in_warning | NOT auto-approved | status approved
                        $icon = $this->whiteThumbUp();
                        $description = "Approved";
                        $requirement_status = $this->completedStatus();

                    } elseif ($requirement_review['status'] == 'declined'){ // document uploaded | submission on_time OR in_warning | NOT auto-approved | status declined

                        if($this->hasRequestedExclusion($requirement_id, $contractor_id)){ // document uploaded | submission on_time OR in_warning | NOT auto-approved | status declined | has requested exclusion

                            if($is_employee_requirement){
                                $exclusion_data = $this->exclusionStatus($requirement_id, '', $role_id);
                            } else {
                                $exclusion_data = $this->exclusionStatus($requirement_id, $contractor_id);
                            }

                            if($submission['created_at'] > $exclusion_data['created_at']){ // if date of upload is newer than exclusion, keep submission info
                                $icon = $this->whiteThumbDown();
                                $description = "Declined";
                                $requirement_status = $this->declinedStatus();
                            } else {
                                $icon = $exclusion_data['thumb'];
                                $description = $exclusion_data['description'];
                                $requirement_status = $exclusion_data['status'];
                            }

                        } else { // document uploaded | submission on_time OR in_warning | NOT auto-approved | status declined | has NOT requested exclusion
                            $icon = $this->whiteThumbDown();
                            $description = "Declined";
                            $requirement_status = $this->declinedStatus();
                        }

                    } elseif ($requirement_review['status'] == 'pending'){// document uploaded | submission on_time OR in_warning | NOT auto-approved | status pending

                        if($this->hasRequestedExclusion($requirement_id, $contractor_id)){ // document uploaded | submission on_time OR in_warning | NOT auto-approved | status pending | has requested exclusion
                            if ($is_employee_requirement) {
                                $exclusion_data = $this->exclusionStatus($requirement_id, '', $role_id);
                            } else {
                                $exclusion_data = $this->exclusionStatus($requirement_id, $contractor_id);
                            }

                            if($submission['created_at'] > $exclusion_data['created_at']){ // if date of upload is newer than exclusion, keep submission info
                                $icon = $this->warningThumb();
                                $description = "Pending Review";
                                $requirement_status = $this->waitingStatus();
                            } else {
                                $icon = $exclusion_data['thumb'];
                                $description = $exclusion_data['description'];
                                $requirement_status = $exclusion_data['status'];
                            }

                        } else { // document uploaded | submission on_time OR in_warning | NOT auto-approved | status pending | has NOT requested exclusion
                            // JIRA DEV-1546 - Change from warning thumb to thumbs up
                            $icon = $this->warningThumb();
                            $description = "Pending Review";
                            $requirement_status = $this->waitingStatus();
                        }

                    }

                }

            }

        } else { // document NOT uploaded

            if($is_employee_requirement){ // employee requirement
                $requested_exclusion = $this->hasRequestedExclusion($requirement_id, $contractor_id, $role_id);
            } else { //corporate requirement
                $requested_exclusion = $this->hasRequestedExclusion($requirement_id, $contractor_id, $resource_id);
            }

            if($requested_exclusion){ //document NOT uploaded | has requested exclusion

                if($is_employee_requirement){
                    $exclusion_data = $this->exclusionStatus($requirement_id, '', $role_id);
                } else {
                    $exclusion_data = $this->exclusionStatus($requirement_id, $contractor_id, $resource_id);
                }

                $icon = $exclusion_data['thumb'];
                $description = $exclusion_data['description'];
                $requirement_status = $exclusion_data['status'];

            } else { // document NOT uploaded | has not requested exclusion
                $icon = $this->warningThumb();
                $description = "Not Actioned";
                $requirement_status = $this->notCompletedStatus();
            }
        }

        return [
            'status' => $requirement_status,
            'icon' => $icon,
            'description' => $description,
            'completion_date' => isset($submission) ? $submission['completion_date'] : null,
            'warning_date' => isset($submission) ? $submission['warning_date'] : null,
            'expiration_date' => isset($submission) ? $submission['expiration_date'] : null
        ];
    }

    /**
     * @param $requirement_id
     * @param $contractor_id
     * @param $role_id
     * @return integer
     * @throws Exception
     */
    private function hasDocumentUploaded($requirement_id, $contractor_id, $role_id=null, $resource_id=null)
    {

        if($role_id){ //employee requirement
            $requirement_history_count = RequirementHistory::where('requirement_id', $requirement_id)
                ->where('contractor_id', $contractor_id)
                ->where('role_id', $role_id)
                ->count();
        } else { //corporate requirement
            if ($resource_id != null) {
                $requirement_history_count = RequirementHistory::where('requirement_id', $requirement_id)
                //->where('contractor_id', $contractor_id)
                ->where('resource_id', $resource_id)
                ->count();
            }else {
                $requirement_history_count = RequirementHistory::where('requirement_id', $requirement_id)
                ->where('contractor_id', $contractor_id)
                ->whereNull('resource_id')
                ->count();
            }
            
        }

        if (!isset($requirement_history_count)){
            throw new Exception("Failed determining if document has been uploaded for requirement $requirement_id and contractor $contractor_id.");
        }

        return $requirement_history_count;
    }

    /**
     *
     * DEPRECATED
     *
     * @param $requirement_id
     * @param $contractor_id
     * @param null $role_id
     * @return bool
     */
    private function fixRequirementHistoryCreatedDate($requirement_id, $contractor_id, $role_id=null)
    {
        if($role_id) {
            $requirement_histories = RequirementHistory::where('requirement_id', $requirement_id)
                ->where('contractor_id', $contractor_id)
                ->where('requirement_id', $requirement_id)
                ->orderBy('id', 'DESC')
                ->get();
        } else {
            $requirement_histories = RequirementHistory::where('requirement_id', $requirement_id)
                ->where('contractor_id', $contractor_id)
                ->orderBy('id', 'DESC')
                ->get();
        }

        foreach ($requirement_histories as $history) { //going through all requirements and fixing created_at
            if (!isset($history->created_at)) { //created date is not set
                $history->created_at = $history->completion_date; //update created_at with completion date info.
                $history->save();

                $history->refresh(); //update model
            }
        }

        return true;
    }

    /**
     * @param $requirement_id
     * @param $contractor_id
     * @param null $role_id
     * @return string
     * @throws Exception
     */
    private function submissionInfo($requirement_id, $contractor_id, $role_id=null, $resource_id=null)
    {
        $requirement = Requirement::find($requirement_id);

        if($role_id) {
            $latest_requirement_history = RequirementHistory::where('requirement_id', $requirement_id)
                ->where('contractor_id', $contractor_id)
                ->where('role_id', $role_id)
                ->orderBy('id', 'DESC')
                ->first();
        } else {
            if ($resource_id != null) {
                //resource requirement
                $latest_requirement_history = RequirementHistory::where('requirement_id', $requirement_id)
//                    ->where('contractor_id', $contractor_id)
                    ->where('resource_id', $resource_id)
                    ->orderBy('id', 'DESC')
                    ->first();
            } else {
                //corporate requirement
                $latest_requirement_history = RequirementHistory::where('requirement_id', $requirement_id)
                    ->where('contractor_id', $contractor_id)
                    ->whereNull('resource_id')
                    ->orderBy('id', 'DESC')
                    ->first();
            }
        }

        $now = Carbon::today()->endOfDay();


            $completion_date = Carbon::createFromFormat('Y-m-d', $latest_requirement_history->completion_date);

            // Our Laravel Version (5.7 atm) doesnt have Carbon 2, which has the Immutable method
            // Doing this extra step to prevent Carbon to change $completion_date
            $temp_expiration_date = clone $completion_date;
            $expiration_date = $temp_expiration_date
                ->addMonths($requirement->renewal_period)
                ->endOfDay();

            // Our Laravel Version (5.7 atm) doesnt have Carbon 2, which has the Immutable method
            // Doing this extra step to prevent Carbon to change $expiration_date
            $temp_warning_date = clone $expiration_date;
            $warning_date = $temp_warning_date
                ->subDays($requirement->warning_period)
                ->startOfDay();



        if (!isset($expiration_date)){
            throw new Exception("Expiration Date not found for requirement $requirement->id and contractor $contractor_id");
        }

        if (!isset($warning_date)){
            throw new Exception("Warning Date not found for requirement $requirement->id and contractor $contractor_id");
        }

        if ($expiration_date->lt($now)) {
            $status = 'expired';
        } elseif ($expiration_date->gte($warning_date) && $expiration_date->lte($now)) {
            $status = 'in_warning';
        } elseif ($expiration_date->gt($now)) {
            $status='on_time';
        } else {
            $status = null;
        }

        return [
            'status' => $status,
            'created_at' => Carbon::parse($latest_requirement_history->created_at)->format('Y-m-d'),
            'completion_date' => Carbon::parse($completion_date)->format('Y-m-d'),
            'warning_date' => Carbon::parse($warning_date)->format('Y-m-d'),
            'expiration_date' => Carbon::parse($expiration_date)->format('Y-m-d'),
        ];

    }

    /**
     * @param $requirement_id
     * @return bool
     * @throws Exception
     */
    private function autoApproved($requirement_id)
    {
        $requirement = Requirement::find($requirement_id);

        if (!isset($requirement->count_if_not_approved)){
            throw new Exception('Failed finding Auto Approved (count if not approved)');
        }

        return (bool)$requirement->count_if_not_approved;
    }

    /**
     * @param $requirement_id
     * @param $contractor_id
     * @return string
     */
    private function reviewStatus($requirement_id, $contractor_id, $role = null, $resource_id=null)
    {

        if ($role) { //employee requirement
            $requirement_history = RequirementHistory::where('requirement_id', $requirement_id)
                ->where('contractor_id', $contractor_id)
                ->where('role_id', $role->id)
                ->orderBy("id", "DESC")
                ->first();
        } else {
            if($resource_id != null){
                $requirement_history = RequirementHistory::where('requirement_id', $requirement_id)
//                    ->where('contractor_id', $contractor_id)
                    ->where('resource_id', $resource_id)
                    ->orderBy("id", "DESC")
                    ->first();
            } else {
//                Log::info("corp reviewStatus");
                $requirement_history = RequirementHistory::where('requirement_id', $requirement_id)
                    ->where('contractor_id', $contractor_id)
                    ->whereNull('resource_id')
                    ->orderBy("id", "DESC")
                    ->first();
            }
        }

        $requirement_review = RequirementHistoryReview::where('requirement_history_id', $requirement_history->id)
            ->orderBy("id", "DESC")
            ->first();

        if (!isset($requirement_review)) {
            $status = 'pending';
        } elseif ($requirement_review->status == 'approved') {
            $status = 'approved';
        } elseif ($requirement_review->status == 'declined') {
            $status = 'declined';
        }

        return ['status' => $status, 'review_date' => ($requirement_review->created_at) ?? null];

    }

    /**
     * @param $requirement_id
     * @param $contractor_id
     * @return bool
     * @throws Exception
     */
    private function hasRequestedExclusion($requirement_id, $contractor_id, $role_id=null, $resource_id=null)
    {

        if($role_id){
            $requested_exclusion = ExclusionRequest::where('requirement_id', $requirement_id)
                ->where('requester_role_id', $role_id)
                ->count();
        } else {
            if($resource_id != null){
                $requested_exclusion = ExclusionRequest::where('requirement_id', $requirement_id)
                    ->where('contractor_id', $contractor_id)
                    ->where('resource_id', $resource_id)
                    ->count();
            } else {
                $requested_exclusion = ExclusionRequest::where('requirement_id', $requirement_id)
                    ->where('contractor_id', $contractor_id)
                    ->count();
            }
        }

        if (!isset($requested_exclusion)){
            throw new Exception("Failed determining if exclusion has been requested for requirement $requirement_id and contractor $contractor_id.");
        }

        return (bool)$requested_exclusion;
    }

    /**
     * @param $requirement_id
     * @param $contractor_id
     * @param null $role_id
     * @return array
     * @throws Exception
     */
    private function exclusionStatus($requirement_id, $contractor_id=null, $role_id=null, $resource_id=null)
    {
        try {
            if ($role_id) {
                $exclusion = ExclusionRequest::where('requirement_id', $requirement_id)
                    ->where('requester_role_id', $role_id)
                    ->latest()
                    ->first();
            } else {
                if($resource_id != null){
                    $exclusion = ExclusionRequest::where('requirement_id', $requirement_id)
                        ->where('contractor_id', $contractor_id)
                        ->where('resource_id', $resource_id)
                        ->latest()
                        ->first();
                } else {
                    $exclusion = ExclusionRequest::where('requirement_id', $requirement_id)
                        ->where('contractor_id', $contractor_id)
                        ->latest()
                        ->first();
                }
            }

            if (!isset($exclusion)) {
                if($role_id){
                    $msg = "and role_id $role_id.";
                } else {
                    $msg = "and contractor $contractor_id.";
                }
                throw new Exception("Failed retrieving exclusion status for requirement $requirement_id " . $msg);
            }

            switch ($exclusion->status) {
                case 'approved': // exclusion approved
                    $thumb = $this->whiteThumbUp();
                    $requirement_status = $this->completedStatus();
                    break;
                case 'waiting': // exclusion has had no response
                    $thumb = $this->warningThumb();
                    $requirement_status = $this->waitingStatus();
                    break;
                case 'rejected': // exclusion rejected
                    $thumb = $this->whiteThumbDown();
                    $requirement_status = $this->declinedStatus();
                    break;
            }

            if ($exclusion->status == 'rejected' && isset($exclusion->responder_note)) {
                $description = "Exclusion " . ucfirst($exclusion->status) . " | " . $exclusion->responder_note;
            } else {
                $description = "Exclusion " . ucfirst($exclusion->status);
            }

            return [
                'description' => $description,
                'thumb' => $thumb,
                'status' => $requirement_status,
                'created_at' => Carbon::parse($exclusion['created_at'])->format('Y-m-d')
            ];

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return null;
        }
    }

    private function whiteThumbUp()
    {
        return 'img/white_thumbs_up.png';
    }

    private function orangeThumb()
    {
        return 'img/orange_thumbs_up.png';
    }

    private function whiteThumbDown()
    {
        return 'img/white_thumbs_down.png';
    }

    private function warningThumb()
    {
        return 'img/white_warning.png';
    }

    private function expiredThumb(){
        return 'img/white_expired.png';
    }

    private function completedStatus()
    {
        return "completed";
    }

    private function notCompletedStatus()
    {
        return "not_completed";
    }

    private function declinedStatus()
    {
        return "declined";
    }

    private function waitingStatus()
    {
        return "waiting";
    }

    private function expiredStatus()
    {
        return "expired";
    }

}
