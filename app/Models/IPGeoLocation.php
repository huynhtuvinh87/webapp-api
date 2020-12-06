<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IPGeoLocation extends Model
{
    protected $table = 'ip_geolocations';
    public $fillable = [
        'ip_address',
        'country_code',
        'source'
    ];
}
