<?php

namespace App\Models;

use App\Models\DynamicForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores the post-submission actions for forms.
 *
 * Given that the result of the `dynamic_form_column_label` is true, apply the action
 * For certain actions, additional information can be stored in `value` such as issuing a new form, notifying users, etc...
 */
class DynamicFormSubmissionAction extends Model
{
    // Timestamps are held in the submission object
    public $timestamps = false;

    public $actions = [
        'accept', // Marks the form as accepted
        'reject', // Marks the form as rejected
        'pending', // Marks the form as pending approval
        // TODO: new form
        // TODO: notify
    ];

    public $fillable = [
        'dynamic_form_id',
        // Id the column_id is set, will use column_id to reference column.
        'dynamic_form_column_id',
        // If column_id not set ,will use column_label to determine column_id
        'dynamic_form_column_label',
        'action',
        'value'
    ];

    public function getActions()
    {
        return $this->actions;
    }

    public function dynamicForm() : BelongsTo
    {
        return $this->belongsTo(DynamicForm::class);
    }

    public function column()
    {
        $columnId = $this['dynamic_form_column_id'];
        $columnLabel = $this['dynamic_form_column_label'];

        $column = null;

        if( isset($columnId) ){
            // If the column_id is set, use the id.
            $column = $this->dynamicForm->columns
                ->where('id', $columnId);
        } else if ( isset($columnLabel ) ){
            // If the id is not set, use the label instead.
            $column = $this->dynamicForm->columns
                ->where('label', $columnLabel);

            $newColumnId = $column->id;

            // Updating column id in DB
            $this['dynamic_form_column_id'] = $newColumnId;
            $this->save();
        }
        // If neither are set, return null

        // Return column
        return $column;

    }
}
