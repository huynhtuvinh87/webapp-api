<?php

namespace App\ViewModels;

use App\Models\ExclusionRequest;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Traits\RequirementStatusTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ViewContractorResourcePositionRequirements extends Model
{
    use RequirementStatusTrait;

    protected $table = "view_contractor_resource_position_requirements";
    protected $appends = ['status', 'requirement_history_id', 'file_id', 'requirement_auto_approved', 'requirement_integration', 'exclusion_notes'];

    protected function getRequirementContentFileAttribute($value)
    {
        if (!$value) {
            return null;
        }

        $ext = pathinfo($value)['extension'];

        if (config('filesystems.default') === "s3") {
            $url =  Storage::temporaryUrl($value, now()
                ->addMinutes(config('filetypes.private_link_life.long')));
        }
        else {
            $url = Storage::url($value);
        }

        //If microsoft file, return preview link
        if ($ext &&
            in_array($ext, config('filetypes.microsoft'), true)){
            $url = "https://view.officeapps.live.com/op/view.aspx?src=".urlencode($url);
        }

        return $url;

    }

    /**
     * Appends the attribute Requirement History Id to obj
     */
    public function getRequirementHistoryIdAttribute()
    {
        $requirement_history = RequirementHistory::where('requirement_id', $this->requirement_id)
            ->where('contractor_id', $this->contractor_id)
            ->latest()
            ->first();
        return ($requirement_history->id) ?? null;
    }

    /**
     * Appends the attribute Requirement File Id to obj
     */
    public function getFileIdAttribute()
    {
        $requirement_history = RequirementHistory::where('requirement_id', $this->requirement_id)
            ->where('contractor_id', $this->contractor_id)
            ->latest()
            ->first();
        return ($requirement_history->file_id) ?? null;
    }

    /**
     * Appends the attribute Auto Approved to obj
     */
    public function getRequirementAutoApprovedAttribute()
    {
        $requirement = Requirement::find($this->requirement_id);
        return $requirement->count_if_not_approved ? true : false;
    }

    /**
     * Appends the attribute Integration Resource to obj
     */
    public function getRequirementIntegrationAttribute()
    {
        $requirement = Requirement::find($this->requirement_id);
        return $requirement->integration_resource_id;
    }

    /**
     * Appends the attribute Exclusion Notes to obj
     */
    public function getExclusionNotesAttribute()
    {
        $notes = [
            'requester' => null,
            'responder' => null
        ];

        if(isset($this->exclusion_request_id)) {
            $exclusion = ExclusionRequest::find($this->exclusion_request_id);

            $notes['requester'] = $exclusion->requester_note;
            $notes['responder'] = $exclusion->responder_note;
        }

        return $notes;
    }

    /**
     * Get Attribute Requirement Status considering:
     *      1. Requirement Status
     *      2. Exclusion Requests
     *      3. Expiration Date
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        return $this->getRequirementStatus($this->requirement_id, $this->contractor_id, null, $this->resource_id);
    }
}
