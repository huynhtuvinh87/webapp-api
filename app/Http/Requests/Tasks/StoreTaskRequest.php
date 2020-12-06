<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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

        $filetypes = config('filetypes.documents_string');
        $maxFileSize = config('filesystems.max_size', 10240);

        return [
            'short_description' => "required|max:30|min:3",
            'long_description' => "max:512",
            'attachments.*' => "file|mimes:$filetypes|max:$maxFileSize",
            "attachments" => "max:3"
        ];
    }
}
