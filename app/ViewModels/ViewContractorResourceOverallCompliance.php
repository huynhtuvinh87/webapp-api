<?php

namespace App\ViewModels;

use App\Models\HiringOrganization;
use App\Models\Resource;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

class ViewContractorResourceOverallCompliance extends Model
{
    protected $table = "view_contractor_resource_overall_compliance";

    protected $appends = ['requirements_past_due_count'];

    /* -------------------------------- Relations ------------------------------- */
    public function resource() {
        return $this->belongsTo(Resource::class);
    }

    // Pending Requirements dial count DOES NOT considers INTERNAL REQUIREMENTS
    // The method below is used to count outstanding requirements in Resources View
    protected function getRequirementsPastDueCountAttribute()
    {
        $pending_count = $this->getAllRequirements()
            ->where('requirement_type', '!=', 'internal_document')
            ->whereNotIn('status.status',['completed', 'waiting'])
            ->unique('requirement_id')
            ->count();
        return ($pending_count) ?? 0;
    }

    // Compliance takes in consideration INTERNAL REQUIREMENTS, although it is not actionable by Contractor
    // The method below is used to calculate compliance in the method `getComplianceAttribute` above
    protected function getRequirementsPastDueCount()
    {
        $pending_count = $this->getAllRequirements()
            ->whereNotIn('status.status',['completed', 'waiting'])
            ->unique('requirement_id')
            ->count();
        return ($pending_count) ?? 0;
    }

    private function getAllRequirements()
    {
        if(!isset($this->resource)){
            // If resource could not be found, return empty array
            return collect([]);
        }
        $requirements = $this->resource->requirements()
            // ->where('requirement_type', '!=', 'internal_document') // Internal Document should not be considered in Employees side
            ->where(function ($query) {
                $query->where('exclusion_status', '!=', "approved")
                    ->orWhereNull('exclusion_status');
            })
            ->get();

        return collect($requirements)->unique('requirement_id');
    }
}
