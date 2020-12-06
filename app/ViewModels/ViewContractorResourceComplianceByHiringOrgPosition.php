<?php

namespace App\ViewModels;

use App\Models\Resource;
use App\Traits\RequirementStatusTrait;
use Illuminate\Database\Eloquent\Model;

class ViewContractorResourceComplianceByHiringOrgPosition extends Model
{
    use RequirementStatusTrait;

    protected $table = "view_contractor_resource_compliance_by_hiring_org_position";

    protected $appends = [
        'requirements_past_due_count',
        'status'
    ];

    protected function getRequirementsPastDueCountAttribute()
    {
        $pending_count = $this->getAllRequirements()->whereNotIn('status.status',['completed', 'waiting'])->unique('requirement_id')->count();
        return ($pending_count) ?? 0;
    }

    private function getAllRequirements()
    {
        $requirements = Resource::find($this->resource_id)->requirements()
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

    public function getStatusAttribute()
    {
        return $this->getRequirementStatus($this->requirement_id, $this->contractor_id, null, $this->resource_id);
    }
}
