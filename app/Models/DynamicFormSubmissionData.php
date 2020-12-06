<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Exception;

/**
 * Stores the submission data that was made.
 *
 * Initially dynamic_form_column_id is null. Is set when columns are searched.
 * ID's arent immediately used as when the data is created, the column may not have an ID. The only thing that is needed initially is the label for the field.
 */
class DynamicFormSubmissionData extends Model
{
    // Setting table name
    // Avoid the auto-generated `...submission_datas` table name
    protected $table = 'dynamic_form_submission_data';

    // Timestamps are held in the submission object
    public $timestamps = false;

    public $fillable = [
        'dynamic_form_submission_id',
        'dynamic_form_column_label',
        'dynamic_form_column_id',
        'value'
    ];

    /**
     * Retrieve the DynamicFormSubmission object
     *
     * @return BelongsTo
     */
    public function dynamicFormSubmission() : BelongsTo
    {
        return $this->belongsTo(DynamicFormSubmission::class);
    }

    /**
     * Retrieve the DynamicForm model from the submission
     *
     * @return DynamicForm
     */
    public function dynamicFormModel() : DynamicForm
    {
        $submission = $this->dynamicFormSubmission
            ->get()
            ->first();

        $submissionForm = $submission->storedDynamicForm();
        return $submissionForm;
    }

    /**
     * Get the associated column based on the dynamic form column ID
     * Also sets the dynamic form column ID (if its not set) based on the label
     *
     * @return HasOne
     */
    public function column() : HasOne
    {
        try {
            if (!isset($this->dynamic_form_column_id)) {
                $this->findColumnByLabelInStoredModel();
            }
        } finally {
            return $this->hasOne(DynamicFormColumn::class, 'dynamic_form_column_id');
        }
    }

    /**
     * Updates the dynamic_form_column_id from the submission model
     * Returns the column id if found
     *
     * @return Int
     */
    public function findColumnByLabelInStoredModel()
    {
        // Getting the column label and error checking
        $columnLabel = $this['dynamic_form_column_label'];
        if( !isset($columnLabel) ){
            throw new Exception("Can't find column ID - label is not set");
        }

        // Getting the stored form model, and error checking
        $formModel = $this->dynamicFormModel();
        if( !isset($formModel) ){
            throw new Exception("Stored model returned nothing: " . $formModel);
        }

        // Getting the columns from the form model, and error checking
        $columns = $formModel->columns;
        if(!isset($columns) || sizeof($columns) == 0){
            throw new Exception("Stored model did not return columns");
        }

        // Getting the relevant column based on the label
        $column = $columns
            ->where('label', $columnLabel)
            ->first();
        if( !isset($column) || !isset($column->id) ){
            throw new Exception("Column was not found (" . $columnLabel . "): " . $columns);
        }

        // Updating the column_id property
        $this['dynamic_form_column_id'] = $column->id;

        // Saving the changes
        $this->save();

        // Returning the column ID
        return $column->id;
    }

}
