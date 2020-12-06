<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubcontractorSurvey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'entity_key',
        'entity_id',
        'role_id',
        'answer',
    ];

    public function company(): MorphTo
    {
        return $this->morphTo('company', 'entity_key', 'entity_id');
    }

    public function role(): HasOne
    {
        return $this->hasOne(Role::class);
    }
}
