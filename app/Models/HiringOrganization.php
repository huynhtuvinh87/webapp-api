<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Log;

class HiringOrganization extends Model
{

    protected $fillable = [
        'name',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
		'website',
		'rating_system',
        'is_active',
    ];

    public function getLogoAttribute($value){
        if (!$value) {
            return null;
        }

        return Storage::url($value);
    }

    public function roles()
    {
        return $this->morphMany('App\Models\Role', 'company', 'entity_key', 'entity_id');
    }

    public function subcontractorSurvey(): MorphMany
    {
        return $this->morphMany('App\Models\SubcontractorSurvey', 'company', 'entity_key', 'entity_id');
    }

    public function workTypes() : BelongsToMany {
        return $this->belongsToMany(WorkType::class);
    }

    public function facilities() : HasMany {
        return $this->hasMany(Facility::class);
    }

    public function departments() : HasMany {
        return $this->hasMany(Department::class);
    }

    public function requirements() : hasMany {
        return $this->hasMany(Requirement::class);
    }

    public function folders() : hasMany {
        return $this->hasMany(Folder::class);
    }

    public function tests() : hasMany {
        return $this->hasMany(Test::class);
    }

    public function positions() : hasMany
    {
        return $this->hasMany(Position::class);
    }

    public function contractors() : BelongsToMany
    {
        return $this->belongsToMany(Contractor::class)->withPivot(['accepted', 'due_date']);
    }

    public function invites() : BelongsToMany
    {
        return $this->belongsToMany(Contractor::class)->wherePivot('accepted', 0)->withPivot('due_date');
    }

    public function ratings() : HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function moduleVisibility() : MorphMany
    {
        return $this->morphMany('App\Models\ModuleVisibility', 'entity');
    }

    public function isModuleVisible($moduleId)
    {
        $module = Module::where('id', $moduleId)
            ->first();

        // If module is not set, return null by default
        if(!isset($module)){
            return null;
        }

        // Grabbing the specific visibility for the hiring org
        $modVis = $module
            ->moduleVisibility
            ->where('entity_type', 'hiring_organization')
            ->where('entity_id', $this->id)
            ->first();

        // If the specific visibility is set, set the return value to be the specific visibility
        if(isset($modVis)){
            $visible = $modVis['visible'];
        } else {
            // If specific vis is not set, return generic
            $visible = $module['visible'];
        }

        return $visible;
    }

    public function owner() : HasOne{
        return $this->hasOne(Role::class, 'entity_id')
            ->where('role', 'owner')
            ->where('entity_key', 'hiring_organization');
    }

    public function owners() : HasMany{
        return $this->hasMany(Role::class, 'entity_id')
            ->where('role', 'owner')
            ->where('entity_key', 'hiring_organization');
    }

}
