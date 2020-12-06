<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileRequirementHistory extends Model
{
    protected $fillable = [
        'requirement_history_id',
        'file_id',
    ];

    public function history() : BelongsTo
    {
        return $this->belongsTo(RequirementHistory::class);
    }

}
