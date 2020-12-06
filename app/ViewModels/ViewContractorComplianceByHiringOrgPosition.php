<?php

namespace App\ViewModels;

use App\Models\Contractor;
use Illuminate\Database\Eloquent\Model;

class ViewContractorComplianceByHiringOrgPosition extends Model
{
    protected $table = "view_contractor_compliance_by_hiring_org_position";

    protected $appends = ['compliance', 'requirements_past_due_count'];

    protected function getComplianceAttribute()
    {

        if (!$this->attributes['requirement_count']) {
            return 100;
        }

        $requirement_count = $this->getAllRequirements()->count();
        if($requirement_count) {
            return floor(($requirement_count - $this->getRequirementsPastDueCountAttribute()) / $requirement_count * 100);
        } else {
            return 0;
        }

        //TODO clean this after deployed and tested
        return floor($this->attributes['requirements_completed_count'] / $this->requirement_count * 100);
    }

    protected function getRequirementsPastDueCountAttribute()
    {
        $pending_count = $this->getAllRequirements()->whereNotIn('status.status',['completed', 'waiting'])->unique('requirement_id')->count();
        return ($pending_count) ?? 0;

        //TODO clean this after deployed and tested
        return $this->attributes['requirement_count'] - $this->attributes['requirements_completed_count'];
    }

    private function getAllRequirements()
    {
        $requirements = Contractor::find($this->contractor_id)->requirements()
            // ->where('requirement_type', '!=', 'internal_document') // Internal Document should not be considered in Contractors side
            ->where('hiring_organization_id', '=', $this->hiring_organization_id)
            ->where('position_id', '=', $this->position_id)
            ->where(function ($query) {
                $query->where('exclusion_status', '!=', "approved")
                    ->orWhereNull('exclusion_status');
            })
            ->get();

        return collect($requirements)->unique('requirement_id');
    }
}
