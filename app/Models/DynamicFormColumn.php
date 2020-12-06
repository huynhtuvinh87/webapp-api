<?php

namespace App\Models;

use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * DynamicFormColumn stores the latest column information for the table
 * TODO: Add in column ordering
 */
class DynamicFormColumn extends Model
{
    public $fillable = [
        'dynamic_form_id',
        'label',
        'description',
        'type',
        'order',
        'transformation',
        'visible_to_contractors',
        'file_id',
        'required',
        "data"
    ];

    // Control types for designing the form
    public $controlTypes = [
        'label', // Label field - displays text on the form
        'text', // Text input field - returns a string
        'numeric', // Numeric input field - returns a number
        'checkbox', // Checkbox - returns true / false
        'transformation',
        'image',
        'textarea',
        'radio',
        'select',
        'date'
    ];

    // Disabling time stamps for columns (Timestamps are held in dynamic_forms)
    public $timestamps = false;

    public function dynamicForm(): BelongsTo
    {
        return $this->belongsTo(DynamicForm::class);
    }

    public function getControlTypes()
    {
        return $this->controlTypes;
    }

    public function file() : BelongsTo{
        return $this->belongsTo(File::class);
    }
}
