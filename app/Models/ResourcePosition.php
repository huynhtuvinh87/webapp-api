<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ResourcePosition extends Model
{

    protected $table = "resource_position";

    protected $fillable = [
        "resource_id",
        "position_id"
    ];

    public function positions() : BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

}
