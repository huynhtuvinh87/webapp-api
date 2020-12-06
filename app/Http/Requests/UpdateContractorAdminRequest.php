<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateContractorAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        $user = Auth::user();

        if (
            ($user->role->role === 'admin' || $user->role->role === 'owner') &&
            $this->route('user')->whereHas('roles', function ($query) use ($user) {
                $query->where('entity_id', $user->role->entity_id);
                $query->where('entity_key', $user->role->entity_key);
            })
        ) {
            return true;
        }


        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "first_name" => "max:30",
            "last_name" => "max:30",
            "email" => "email|unique:users",
            "password" => "confirmed"
        ];
    }
}
