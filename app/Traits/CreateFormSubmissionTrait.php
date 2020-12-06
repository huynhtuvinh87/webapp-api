<?php

namespace App\Traits;

use App\Models\DynamicFormSubmission;
use App\Models\DynamicForm;
use App\Models\DynamicFormSubmissionData;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Models\User;
use Exception;

trait CreateFormSubmissionTrait
{
    public function createSubmission(Request $request, Requirement $requirement)
    {
        // Confirm requirement is a form or internal type
        if ($requirement->type != 'form' && $requirement->type != 'internal_document') {
            throw new Exception("Requirement type is not a form, can't submit against requirement!");
        }

        // Getting dynamic form from requirement
        $dynamicForm = $requirement->form;
        if (!isset($dynamicForm)) {
            throw new Exception("No form is associated with the requirement");
        }

        $userRoleId = $this->getCurrentRoleIDFromRequest($request);
        if ($userRoleId == null || $userRoleId <= 0) {
            // TODO: Throw an exception instead?
            throw new Exception("User role ID was invalid!");
            $user = User::get()->first();
        }
        $contractorId = $request->user()->role->entity_id;

        // Add requirement history entry
        $requirementHistory = RequirementHistory::create([
            "requirement_id" => $requirement->id,
            "completion_date" => now(),
            "role_id" => $userRoleId,
            "contractor_id" => $contractorId,
        ]);

        // Create submission object
        $submission = $this->createSubmissionFromRequest($request, $dynamicForm, $requirementHistory);

        $requestForm = $request->get('form');
        if (!isset($requestForm)) {
            throw new Exception("form was not defined in response");
        }

        if (!isset($requestForm['submission'])) {
            throw new Exception("form.submission was not defined in response");
        }
        $requestSubmission = $requestForm['submission'];

        if (!isset($requestSubmission['data'])) {
            throw new Exception("form.submission.data was not defined");
        }
        $requestData = $requestSubmission['data'];

        // Creating submission data from request
        if (isset($requestData)) {
            foreach ($requestData as $datagram) {
                if (gettype($datagram) != 'array') {
                    throw new Exception("Datagram was not type of array: " . json_encode($datagram));
                }
                $data = new DynamicFormSubmissionData($datagram);
                $data['dynamic_form_submission_id'] = $submission->id;
                $data->save();
            }
        }

        // Store submission with the current users ID
        $submission->save();

        $this->runActions($dynamicForm, $submission, $dynamicForm->actions);

        // Return the result from the readSubmission action
        return $this->readSubmission($request, $submission);
    }

    /**
     * Updates the submission model
     * Returns the same results as readSubmission, but with the updated data from the database
     *
     * @param Request $request
     * @param DynamicFormSubmission $originalSubmission
     * @return void
     */
    public function updateSubmission(Request $request, DynamicFormSubmission $originalSubmission)
    {
        $originalData = $originalSubmission->data;

        // Getting new submission from request
        $newSubmission = $request->get('submission');
        if (
            !isset($newSubmission) ||
            !isset($newSubmission['id']) ||
            !isset($newSubmission['dynamic_form_id']) ||
            !isset($newSubmission['data']) ||
            !isset($newSubmission['state']) ||
            $newSubmission['id'] != $originalSubmission->id
        ) {
            throw new Exception("Submission was not defined properly \n\n" . json_encode($newSubmission));
        }

        $newSubmissionData = $newSubmission['data'];
        if (!isset($newSubmissionData)) {
            throw new Exception("Submission data was not found");
        }

        // Making sure state is a valid entry
        $validStates = $originalSubmission->getSubmissionStates();
        if (!in_array($newSubmission['state'], $validStates)) {
            throw new Exception("State was not valid!");
        }

        try {
            DynamicFormSubmission::where('id', $originalSubmission->id)
                ->update([
                    'state' => $newSubmission['state'],
                    'modify_role_id' => $newSubmission['modify_role_id'],
                    'dynamic_form_model' => $newSubmission['dynamic_form_model'],
                ]);
        } catch (Exception $e) {
            throw new Exception("Failed to save updated submission \n" . $e);
        }

        // $updatedSubmissionData = $this->loadRequestToModel($newSubmissionData, $originalData);

        // foreach ($updatedSubmissionData as $key => $data) {
        //     $dbData = DynamicFormSubmissionData::where('id', $data['id'])->first();
        //     $updatedData = $this->loadRequestToModel($data, $dbData);
        //     $updatedData->save();
        // }

        // Getting an updated copy of the submissions from the server
        if (!isset($originalSubmission->id)) {
            throw new Exception("Original submission ID was not defined");
        }
        $latestSubmissions = DynamicFormSubmission::where('id', $originalSubmission->id)->first();

        return $this->readSubmission($request, $latestSubmissions);
    }

    protected function getCurrentRoleIDFromRequest($request)
    {
        $user = $request->user();
        if ($user == null) {
            throw new Exception("User was null from request");
        }

        return $user->role->id;
    }

    /**
     * Creates a submission object from the request body with the created user ID
     * NOTE: Does not store the submission in the DB
     *
     * @param [type] $request
     * @return void
     */
    protected function createSubmissionFromRequest($request, $dynamicForm, $requirementHistory)
    {
        // Getting user ID from request
        $submissionUserId = $this->getCurrentRoleIDFromRequest($request);

        // Getting submission request data
        $formRequest = $request->get('form');
        if (!isset($formRequest)) {
            throw new Exception("Form was not defined");
        }

        $submissionRequest = $formRequest['submission'];
        if (!isset($submissionRequest)) {
            throw new Exception("Submission was not defined: " . $submissionRequest);
        }

        // Setting create_role_id prop to be user ID from request
        $submissionRequest['create_role_id'] = $submissionUserId;
        $submissionRequest['dynamic_form_id'] = $dynamicForm->id;
        $submissionRequest['dynamic_form_model'] = json_encode($dynamicForm->getResponseModel());
        // creating submission and returning it

        $submissionRequest['requirement_history_id'] = $requirementHistory->id;

        return DynamicFormSubmission::create($submissionRequest);
    }

    /**
     * Get the list of actions from a submission, and apply them
     * TODO: Define what to do when actions are run
     *
     * @param DynamicFormSubmission $dynamicFormSUbmission
     * @return void
     */
    public function runActions(DynamicForm $form, DynamicFormSubmission $submission, $actions)
    {
        // Get relevant actions from submission
        if (!isset($actions)) {
            throw new Exception("Actions was not defined. " . json_encode($actions));
        }

        foreach ($actions as $action) {
            $doAction = $this->checkIfAction($submission, $action);
            if ($doAction) {
                $submission = $this->updateSubmissionState($submission, $action);
            }
        }
        return $submission;
    }

    /**
     * @param DynamicFormSubmission $dynamicFormSubmission
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function readSubmission(DynamicFormSubmission $dynamicFormSubmission)
    {
        $liveForm = $dynamicFormSubmission->dynamicForm;
		$storedForm = $dynamicFormSubmission->storedDynamicForm();

		if(!isset($storedForm)){
			throw new Exception("Stored form was not found");
		}

        $transformResults = $this->runTransformations($storedForm, $dynamicFormSubmission);

        // Calling data to have it load into dynamicFormSubmission
        $submissionData = $dynamicFormSubmission->data;

        // Passing in new transformed results into data model
        foreach ($transformResults as $transformData) {
            $submissionData->push($transformData);
        }

        return [
            'submission' => $dynamicFormSubmission,
            'stored_dynamic_form' => $storedForm,
        ];
    }

    /**
     * Check to see if action should be run.
     * Returns boolean
     *
     * @param DynamicFormSubmission $submission
     * @param DynamicFormSubmissionAction $action
     * @return bool
     */
    public function checkIfAction(
        DynamicFormSubmission $submission,
        DynamicFormSubmissionAction $action
    ): bool {
        $assocData = $submission->data
            ->where('dynamic_form_column_label', $action->dynamic_form_column_label)
            ->first();

        if (!isset($assocData)) {
            return false;
            // throw new Exception("Associated submission data for the label '" . $action->dynamic_form_column_label . "' could not be found.\nData:" . json_encode($submission->data));
        }

        return $assocData->value == 'true' ||
            $assocData->value == true ||
            $assocData->value == '1';
    }

    public function updateSubmissionState(DynamicFormSubmission $submission, $action)
    {
        if(!isset($submission) || !isset($submission->id)){
            throw new Exception("Submission ID was not defined");
        }
        switch ($action->action) {
            case "accept":
                $state = "accepted";
                break;
            case "reject":
                $state = "rejected";
                break;
            case "pending":
                $state = $action->action;
                break;
            default:
                throw new Exception("No state has been defined for \"" . $action->action . "\" actions.\n" . json_encode($action));
        }

        $updateSubmission = DynamicFormSubmission::findOrFail($submission->id);
        $updateSubmission->state = $state;
        $updateSubmission->save();

        return $submission;
    }

    /**
     * Starts the transformations
     * Returns an array for DynamicFormSubmissionData entries based on the form and submission model passed in
     *
     * @param DynamicForm $form
     * @param DynamicFormSubmission $submission
     * @return array DynamicFormSubmissionData
     */
    public function runTransformations(DynamicForm $form, DynamicFormSubmission $submission): array
    {
        $transformRes = [];
        $columns = $form['columns'];

        foreach ($columns as $column) {
            $columnTransform = isset($column['transformation']) ? $column['transformation'] : null;

            // If column has a transformation
            if (isset($columnTransform)) {

                // Resolve any references in transformation
                $resolvedTransformString = $this->applyTransformation(json_decode($columnTransform), $submission);

                // Compute transformation JSON
                // NOTE: Tried getting JSONLogic::apply(...) to work using `use JWadhams\JsonLogic`, but for some reason, it doesn't work
                $transformationRes = \JWadhams\JsonLogic::apply($resolvedTransformString);

                $sub_data = DynamicFormSubmissionData::where('dynamic_form_column_id', $column->id)
                    ->where('dynamic_form_submission_id', $submission->id)
                    ->first();
                $sub_data->value = $transformationRes;
				$sub_data->save();

                // Store result in value for submission
                // Creating new data entry
                $transformNewData = new DynamicFormSubmissionData([
                    'dynamic_form_submission_id' => $submission->id,
                    'dynamic_form_column_label' => $column->label,
                    'dynamic_form_column_id' => $column->id,
                    'value' => $transformationRes,
                ]);

                array_push($transformRes, $transformNewData);
            }
        }

        return $transformRes;
    }

    /**
     * Checks to see if there are any references, if there are, resolve them
     *
     * @return void
     */
    protected function applyTransformation($transformationObject, DynamicFormSubmission $submission)
    {
        $allRef = [];

        $objectType = gettype($transformationObject);

        if ($objectType == "object" || $objectType == "array") {
            // If its an object or array, break down elements and call self

            // Converting to array
            $objAsArray = (array) $transformationObject;

            // Calling self on each value of array / value
            foreach ($objAsArray as $key => $val) {
                // NOTE: RECURSION HERE!!!
                $objAsArray[$key] = $this->applyTransformation($val, $submission);
            }

            return $objAsArray;
        } elseif ($objectType == "string") {

            // Check if string matches the {{ transformation query }} format
            $strMatchCount = preg_match("/\{\{(.*)\}\}/", $transformationObject, $strMatches);

            // If it matches, then get label value
            if ($strMatchCount) {
                $transformation_query = $strMatches[1];

                return $this->resolveTransformation($transformation_query, $submission);
            }
        }

        // If its just a regular value, then return itself
        return $transformationObject;
    }

    /**
     * Finds a submission data entry based on a given label
     *
     * @param string $label
     * @param DynamicFormSubmission $submission
     * @return void
     */

    protected function resolveTransformation(string $transformation_query, DynamicFormSubmission $submission)
    {

        if (!isset($submission)) {
            throw new Exception("Submission was null for submission id of " . $submission->id);
        }

        if (!isset($transformation_query)) {
            throw new Exception("transformation query was null for submission id of " . $submission->id);
        }

        // Getting datagram from submission and label
        $submissionData = $submission->data
            ->where('dynamic_form_column_id', $transformation_query)
            ->first();

        // Error checking - making sure the data is not null
        if (!isset($submissionData)) {
            throw new Exception(__METHOD__ . ": Submission data was null for submission id of " . $submission->id . ", and query: " . $transformation_query);
            return null;
        }

        $submissionDataVal = $submissionData->value;

        return $submissionDataVal;
    }
}
