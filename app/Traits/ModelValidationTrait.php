<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait ModelValidationTrait
{
    /**
     * Applies validation logic on itself.
     * Returns an object with information regarding prop issues
     * {
     *      isValid: Boolean,
     *      errors: [
     *          {
     *              key: "...",
     *              message: "..."
     *          }
     *      ]
     * }
     *
     * @return Object
     */
    public function validateModel(Collection $validationRules)
    {
        $isValid = false;

        /**
         * List of failed validations
         */
        $badValidation = collect($validationRules)
            ->reject(function ($validation) {return $validation['isValid'];});

        return $this->validationResponse($badValidation);
    }

    /**
     * Generates a validation response from the server to the front end
     *
     * @param [type] $failedValidation
     * @return void
     */
    public function validationResponse($failedValidation)
    {
        $response = [
            "isValid" => $failedValidation->count() == 0,
            "errors" => $failedValidation->count() > 0 ? $failedValidation : null,
        ];

        return $response;
    }

    /**
     * Generates a validation object
     *
     * @param [type] $value
     * @param [type] $isValid
     * @param [type] $failureMessage
     * @return void
     */
    protected function validationObj($value, $isValid, $failureMessage = null)
    {
        return [
            'value' => $value,
            'isValid' => $isValid,
            'error' => $failureMessage ?? null,
        ];
    }


    /**
     * Example of a validator that passes
     *
     * @return void
     */
    protected function validateGoodExample()
    {
        $isValid = true;
        $error = null;

        if (!$isValid) {
            $error = "The validateGoodExample failed";
        }

        return $this->validationObj($this->access_level, $isValid, $error);

    }
}
