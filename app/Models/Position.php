<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{

    protected $fillable = [
        'name',
        'hiring_organization_id',
        'auto_assign',
        'position_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'integer',
        'hiring_organization_id' => 'integer',
        'auto_assign' => 'integer'
    ];

    public function requirements() : BelongsToMany
    {
        return $this->belongsToMany(Requirement::class)->withTimestamps();
    }

    public function hiringOrganization() : BelongsTo
    {
        return $this->belongsTo(HiringOrganization::class);
    }

    public function facilities() : BelongsToMany
    {
        return $this->belongsToMany(Facility::class)->withTimestamps();
    }
    public function resourcePositions() : HasMany
    {
        return $this->hasMany(ResourcePosition::class);
    }
    public function roles() : BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
    public function contractors() : BelongsToMany
    {
        return $this->belongsToMany(Contractor::class)->withTimestamps();
    }
}
