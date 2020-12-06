<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function company(){
        return $this->belongsTo(HiringOrganization::class);
    }

    public function admins(){
        return $this->belongsToMany(Role::class);
    }

    public function requirements(){
        return $this->belongsToMany(Requirement::class);
    }

    public function folders() : BelongsToMany
    {
        return $this->belongsToMany(Folder::class);
    }
}
