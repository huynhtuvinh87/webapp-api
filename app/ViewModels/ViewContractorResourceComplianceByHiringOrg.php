<?php

namespace App\ViewModels;

use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ViewContractorResourceComplianceByHiringOrg extends Model
{
    protected $table = "view_contractor_resource_compliance_by_hiring_org";

    protected $appends = [
        // 'compliance',
        'requirements_past_due_count'
    ];

    /* -------------------------------- Relations ------------------------------- */

    public function hiringOrganization()
    {
        return $this->hasOne(HiringOrganization::class);
    }

    public function resource() {
        return $this->belongsTo(Resource::class);
    }

    /* ------------------------------- Attributes ------------------------------- */

    protected function getRequirementsPastDueCountAttribute()
    {
        $pending_count = $this->getAllRequirements()->whereNotIn('status.status',['completed', 'waiting'])->unique('requirement_id')->count();
        return ($pending_count) ?? 0;
    }

    private function getAllRequirements()
    {
        if(!isset($this->resource)){
            return collect([]);
        }

        $requirements = $this->resource->requirements()
            // ->where('requirement_type', '!=', 'internal_document') // Internal Document should not be considered in Contractors side
            ->where('hiring_organization_id', '=', $this->hiring_organization_id)
            ->where(function ($query) {
                $query->where('exclusion_status', '!=', "approved")
                    ->orWhereNull('exclusion_status');
            })
            ->get();

        return collect($requirements)->unique('requirement_id');
    }
}
