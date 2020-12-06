<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\ModuleVisibility;

class Module extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'visible'
    ];

    public function moduleVisibility() : HasMany{
        return $this->hasMany(ModuleVisibility::class);
    }
}
