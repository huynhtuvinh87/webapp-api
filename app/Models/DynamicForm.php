<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DynamicFormColumn;

/**
 * DynamicForm is the root for each form.
 */
class DynamicForm extends Model
{
    public $fillable = [
        'title', // Title of the form
        'description', // Description of the form
        'create_role_id', // Who created the form
        'modify_role_id', // Who last modified the form
        "hiring_organization_id",
        "can_edit"
    ];

    /**
     * evaluateExpression takes in a JSON model based on https://github.com/jwadhams/json-logic-php/ and returns the result
     *
     * @param String $jsonExpression
     * @return void
     */
    public function evaluateExpression(String $jsonExpression)
    {
        $result = JWadhams\JsonLogic::apply(json_decode($jsonExpression, false));
        return $result;
    }

    /**
     * columns refers to dynamic_form_column table
     *
     * @return HasMany
     */
    public function columns() : HasMany
    {
        return $this->hasMany(DynamicFormColumn::class);
    }

    /**
     * submissions refers to dynamic_form_submission
     *
     * @return HasMany
     */
    public function submissions() : HasMany
    {
        return $this->hasMany(DynamicFormSubmission::class);
    }

    public function actions() : HasMany{
        return $this->hasMany(DynamicFormSubmissionAction::class);
    }

    /**
     * Returns the form and column information to be used in responses back to the client
     * NOTE: Column information must be in the database
     */
    public function getResponseModel($showContractorColumns = true)
    {
        // // Returning form / column model

        $thisForm = $this
            ->with(array('columns'=>function($query) use ($showContractorColumns){
                if(!$showContractorColumns){
                    $query->where('visible_to_contractors', $showContractorColumns);
                }
            }))
            ->with('columns.file')
            ->with('actions')
            ->where('id', $this->id)
            ->first();

        $responseObj = ['form' => $thisForm];

        return $responseObj;
    }

}
