<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Module;

class ModuleVisibility extends Model
{
    //
    protected $fillable = [
        'module_id',
        'entity_key',
        'entity_id',
        'visible'
    ];

    public function module(): BelongsTo{
        return $this->belongsTo(Module::class);
    }

    public function entity() : MorphTo
    {
        return $this->morphTo();
    }

}
