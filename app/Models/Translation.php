<?php

namespace App\Models;

use App\Traits\ExternalAPITrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

class Translation extends Model
{
    use SoftDeletes;
    use ExternalAPITrait;

    protected $fillable = [
        'source_text',
        'source_lang',
        'target_text',
        'target_lang',
        'reference',
        'environment',
    ];

    public function getTranslationAttribute()
    {
        return $this->target_text;
    }

}
