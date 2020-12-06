<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Facility extends Model
{
    protected $fillable = [
        'name',
        'description',
		'notification_email',
		'hiring_organization_id',
        'display_on_registration',
    ];

    public function company() : BelongsTo
    {
        return $this->belongsTo(HiringOrganization::class);
    }

    public function admins() : BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function positions() : BelongsToMany
    {
        return $this->belongsToMany(Position::class)->withTimestamps();
    }

    public function contractors() : BelongsToMany
    {
        return $this->belongsToMany(Contractor::class);
    }

    public function resources() : BelongsToMany
    {
        return $this->belongsToMany(Resource::class);
    }
}
