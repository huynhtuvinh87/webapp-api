<?php

namespace App\Models;

use App\ViewModels\ViewContractorResourceComplianceByHiringOrg;
use App\ViewModels\ViewContractorResourceComplianceByHiringOrgPosition;
use App\ViewModels\ViewContractorResourceOverallCompliance;
use App\ViewModels\ViewContractorResourcePositionRequirements;
use App\ViewModels\ViewContractorResourcesPositionsRequirements;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;


class Resource extends Model
{

    protected $fillable = [
        'name',
        'contractor_id',
        'is_active'
    ];

    public function requirements()  : HasMany
    {
        return $this->hasMany(ViewContractorResourcesPositionsRequirements::class);
    }

    public function hiringOrganization(): BelongsTo
    {
        return $this->belongsTo(HiringOrganization::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class)->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function positions() : BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'resource_position')->withTimestamps();
    }

    public function overallCompliance()
    {
        return $this->hasOne(ViewContractorResourceOverallCompliance::class);
    }

    public function complianceByHiringOrganization() : HasMany
    {
        return $this->hasMany(ViewContractorResourceComplianceByHiringOrg::class);
    }

    public function complianceByHiringOrganizationPositions() : HasMany
    {
        return $this->hasMany(ViewContractorResourceComplianceByHiringOrgPosition::class);
    }

    public function resourceRequirements() : HasMany
    {
        return $this->hasMany(ViewContractorResourcePositionRequirements::class);
    }
}
