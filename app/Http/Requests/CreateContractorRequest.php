<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateContractorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'invite_code' => 'sometimes|exists:contractor_hiring_organization,invite_code',
            'name' => 'required|string|min:3',
            'email' => 'required|email|min:3',
            'password' => 'required|confirmed|min:8',
            'address' => 'required|string|min:6|max:255',
            'state' => 'required|string|min:2|max:255',
            'country' => 'required|string|min:2|max:255',
            'postal_code' => 'required|string|min:3|max:255',
            'hiring_organization_id' => 'required|exists:hiring_organizations,id',
            'hiring_organization_facility_ids' => 'array|max:50',
            'hiring_organization_facility_ids.*' => 'numeric'
        ];
    }
}
