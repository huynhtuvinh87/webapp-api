<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Rules\Website;

class HiringOrganizationController extends Controller
{

    public function index(Request $request){
        return response($request->user()->role->company()->select([
            'name',
            'phone',
            'address',
            'city',
            'state',
            'country',
            'postal_code',
            'website',
			'logo',
			'rating_system',
			'form_rating_requirement_id'
        ])->first());
    }

    public function update(Request $request){

        $this->validate($request, [
            'name' => [
                'max:30',
                Rule::unique('hiring_organizations')->ignore($request->user()->role->company->id)
            ],
            'phone' => 'max:15',
            'address' => 'string|max:50',
            'city' => 'string|max:30',
            'state' => 'string|max:30',
            'country' => 'string|max:30',
            'postal_code' => 'max:12',
            'website' => [new Website]
        ]);

        $update = $request->user()->role->company()->update($request->only([
            'name',
            'phone',
            'address',
            'city',
            'state',
            'country',
            'postal_code',
            'website',
        ]));

        return response($request->user()->role->company()->first());

    }

    public function updateLogo(Request $request){

        $this->validate($request, [
            'logo' => 'required|image'
        ]);

        $company = $request->user()->role->company;

        $path = 'logos/hiring-organization/'.$company->id;

        $name = Storage::putFileAs($path, $request->file('logo'), $company->name.'.'.$request->file('logo')->getClientOriginalExtension(), 'public');

        $company->logo = $name;

        $company->logo_file_name = $company->name;

        $company->logo_file_ext = $request->file('logo')->getClientOriginalExtension();

        $company->save();

        return response(['company' => $company]);

    }
}
