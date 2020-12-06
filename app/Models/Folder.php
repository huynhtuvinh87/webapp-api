<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'hiring_organization_id',
        'name',
    ];

    public function contractors(): BelongsToMany
    {
        return $this->belongsToMany(Contractor::class)->withTimestamps();
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class)->withTimestamps();
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)->withTimestamps()->withTimestamps();
    }

}
