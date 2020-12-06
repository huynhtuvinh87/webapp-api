<?php

namespace App\Models;

use App\Models\DynamicForm;
use App\Models\DynamicFormSubmissionData;
use App\Models\RequirementHistory;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

/**
 * DynamicFormSubmission Stores the submission information for a given form
 * It also stores the instance of the form when the submission was made (`storedDynamicForm()`)
 */
class DynamicFormSubmission extends Model
{
    public $fillable = [
        'dynamic_form_id',
        'create_role_id', // Who created the submission
        'modify_role_id', // Who last modified the submission
        'dynamic_form_model', // Model submission was submitting to
        'requirement_history_id', // Relation to requirement history
    ];

    private $submissionStates = [
        'rejected',
        'pending',
        'accepted',
    ];

    public function dynamicForm(): BelongsTo
    {
        return $this->belongsTo(DynamicForm::class);
    }

    /**
     * Returns the DynamicForm that was stored in the database for when the submission was made
     *
     * @return DynamicForm
     */
    public function storedDynamicForm(): DynamicForm
    {
        $model = json_decode($this['dynamic_form_model'])->form;
        $formObj = new DynamicForm((array) $model);

        // Adding columns
        if (!isset($model)) {
            Log::debug(__METHOD__, [
                'dynamic_form_submission_id' => $this->id,
                'dynamic_form_model' => $this['dynamic_form_model'],
            ]);
            throw new Exception('dynamic_form_model could not be decoded / could not get form from dynamic_form_model');
        }

        $columns = $model->columns;
        if (isset($columns) && sizeof($columns) > 0) {
            foreach ($columns as $column) {
                // Create new column objet
                $columnObj = new DynamicFormColumn((array) $column);
                // Add it to form object
                $columnObj->id = $column->id;

                // If type is image, load the file
                if ($columnObj->type == 'image' && isset($columnObj->file_id)) {
                    $columnObj->file = File::where('id', $columnObj->file_id)->first();
                }

                $formObj->columns->push($columnObj);
            }
        }

        // Adding actions
        try {
            $actions = $model->actions;
            if (isset($actions) && sizeof($actions) > 0) {
                foreach ($actions as $action) {
                    // Create new column objet
                    $columnObj = new DynamicFormColumn((array) $action);
                    // Add it to form object
                    $formObj->actions->push($columnObj);
                }
            }
        } catch (Exception $e) {
            // Do nothing - no actions
        }

        // Adding ID
        $formObj->id = $this->id;
        return $formObj;
    }

    public function data(): HasMany
    {
        return $this->hasMany(DynamicFormSubmissionData::class, 'dynamic_form_submission_id');
    }

    public function getResponseModel()
    {
        return $this->dynamicForm()->getResponseModel();
    }

    public function getSubmissionStates()
    {
        return $this->submissionStates;
    }

    public function requirementHistory(): BelongsTo
    {
        return $this->belongsTo(RequirementHistory::class, 'requirement_history_id');
    }
}
