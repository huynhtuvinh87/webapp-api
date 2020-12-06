<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExclusionRequest extends Model
{
    protected $fillable = [
        "requirement_id",
        "requester_role_id",
        "requested_at",
        "requester_note",
        "contractor_id",
        "status",
        "response_role_id",
        "responder_note",
        "responded_at"
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requirement() : BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
