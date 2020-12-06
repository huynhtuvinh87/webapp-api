<?php

namespace App\Traits;

use App\Models\File;
use App\Traits\FileTrait;
use Exception;
use Illuminate\Http\Request;

trait GeolocationTrait
{
    public function getLocationFromIp($ipAddress){
        // Validate IP address is proper format

        // TODO: Implement geolocation finding by IP
        $location = 'us';

        return $location;
    }
}
