<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $fillable = [
        'route',
        'ip_address',
        'method',
        'token',
        'body',
        'user'
    ];
}
