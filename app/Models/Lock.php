<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'entity_key',
        'entity_id',
        'locker_role_id',
        'ends_at',
    ];

    public function requirement_histories(): HasMany
    {
        return $this->hasMany(RequirementHistory::class);
    }
}
