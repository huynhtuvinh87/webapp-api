<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use App\Models\File;

class RequirementHistory extends Model
{
    protected $fillable = [
        "requirement_id",
        "completion_date",
        "certificate_file",
        "original_file_name",
        "role_id",
        "contractor_id",
        "certificate_file_ext",
        "valid",
        'file_id',
        'resource_id'
    ];

    public function requirement() : BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function file() : BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function files()
    {
        return $this->belongsToMany(File::class)->withTimestamps();
    }

    public function answers(){
        return $this->hasMany(Answer::class);
    }

    public function review() : HasMany
    {
        return $this->hasMany(RequirementHistoryReview::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function dynamicFormSubmission(): HasOne{
        return $this->hasOne(DynamicFormSubmission::class);
    }

    public function lock(): BelongsTo
    {
        return $this->belongsTo(Lock::class, 'entity_id')
            ->where('entity_key', 'requirement_history');
    }
}
