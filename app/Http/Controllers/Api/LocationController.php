<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    public function countries($id = null){

        if (!$id){
            return response(['countries' => Country::get()]);
        }

        Return response(['countries' => Country::find($id)]);
    }

    public function state($id = null){
        if (!$id){
            return response(['states' => State::get()]);
        }

        return response(['states' => Country::find($id)->states]);
    }
}
