<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractorRequest extends FormRequest
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
            'name' => 'max:60',
            'phone' => 'max:15',
            'address' => 'max:50',
            'city' => 'max:30',
            'state' => 'max:30',
            'country' => 'max:30',
            'postal_code' => 'max:12'
        ];
    }
}
