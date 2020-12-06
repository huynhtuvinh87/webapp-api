<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequirementHistoryReview extends Model
{
    protected $fillable = [
        'requirement_history_id',
        'approver_id',
        'notes',
        'status',
        'status_at'
    ];

    public function history() : BelongsTo
    {
        return $this->belongsTo(RequirementHistory::class);
    }

    public function approver() : BelongsTo
    {
        return $this->belongsTo(Role::class, 'approver_id');
    }
}
