<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model
{
    use SoftDeletes;

    protected $dates = [
        'deleted_at',
        'updated_at',
        'created_at',
    ];

    public $fillable = [
        'file_path',
        'environment',
        'updated_at',
        'created_at',
    ];
}
