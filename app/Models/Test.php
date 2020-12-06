<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'html',
        'max_tries',
        'min_passing_criteria',
    ];

    protected $casts = [
        'max_tries' => 'integer',
        'min_passing_criteria' => 'integer',
    ];

    public function questions() : HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function hiringOrganization() : BelongsTo
    {
        return $this->belongsTo(HiringOrganization::class);
    }

    public function requirements() : HasMany
    {
        return $this->hasMany(Requirement::class, 'integration_resource_id');
    }
}
