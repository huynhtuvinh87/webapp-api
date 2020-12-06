<?php

namespace App\Http\Requests\Tasks;

use App\Models\Task;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CompleteTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::id() === $this->route('task')->assigned_to;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $extensions = config('filetypes.documents_string');
        $maxFileSize = config('filesystems.max_size', 10240);

        return [
            "completion_date" => "required|date",
            "completion_description" => "max:512",
            'attachments.*' => "file|max:$maxFileSize|mimes:$extensions",
            'attachments' => "max:3"
        ];
    }
}
