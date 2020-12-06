<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DynamicForm;
use App\Models\DynamicFormSubmission;
use App\Models\DynamicFormSubmissionData;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Role;
use App\Models\Rating;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use App\Traits\RatingControllerTrait;
use Log;
use DB;

// QUESTION: Need the functions to get the form and form columns. Does it follow the programs syntax to include other controllers?

/**
 * Handles the data for the dynamic form
 * TODO: Move all computation to another file - Providers? Model? Not too sure
 */
class DynamicFormSubmissionController extends Controller
{
	use RatingControllerTrait;

    // ==================== Submission CRUD API Functions ==================== //

    /**
     * Creates a submission on a specified form ID
     *
     * @param Request $request
     * @param Int $formId
     * @param String $formData
     * @return void
     */
    public function createSubmission(Request $request, Requirement $requirement)
    {

        try {

        // Confirm requirement is a form or internal type
        if ($requirement->type != 'form' && $requirement->type != 'internal_document') {
            throw new Exception("Requirement type is not a form, can't submit against requirement!");
        }

        // Getting dynamic form from requirement
        $dynamicForm = $requirement->form;
        if (!isset($dynamicForm)) {
            throw new Exception("No form is associated with the requirement");
        }

        if($request->has('type') && ($request->get('type') == 'internal_form')){ // its an internal form

            $contractor_id = null;
            if($request->get('contractor_id')){
                $contractor_id = $request->get('contractor_id');
            } else {
                if($request->get('role_id')) {
                    $role = Role::find($request->get('role_id'));
                    $contractor_id = $role->entity_id;
                } else {
                    throw new Exception("Submitting form, but contractor ID isnt found");
                }
            }

            $userRoleId = $request->get('role_id');
            $contractorId = $contractor_id;
            $resourceId = $request->get('resource_id');

        } else {

            $userRoleId = $this->getCurrentRoleIDFromRequest($request);
            if ($userRoleId == null || $userRoleId <= 0) {
                // TODO: Throw an exception instead?
                throw new Exception("User role ID was invalid!");
                $user = User::get()->first();
            }
            $contractorId = $request->user()->role->entity_id;
            $resourceId = $request->get('resource_id');

        }

        // Add requirement history entry
        $requirementHistory = RequirementHistory::create([
            "requirement_id" => $requirement->id,
            "completion_date" => now(),
            "role_id" => $userRoleId,
            "contractor_id" => $contractorId,
            "resource_id" => $resourceId,
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

        $storedForm = $submission->storedDynamicForm();
        $transformResults = $this->runTransformations($storedForm, $submission);

        // Store submission with the current users ID
        $submission->save();
		$submission = $submission->fresh();

        $this->runActions($dynamicForm, $submission, $dynamicForm->actions);

		$contractor = Contractor::where('id', $contractorId)->first();
		$role = Role::where('id', $userRoleId)->first();
		$hiringOrg = HiringOrganization::where('id', $dynamicForm->hiring_organization_id)->first();

        // If the form is for a rating, create rating entry
        if ($this->isRatingForm($dynamicForm, $hiringOrg)) {
        	// Creating rating
        	$this->ratingCreateFromForm(
        		$submission,
        		$contractor,
        		$hiringOrg,
        		$role
        	);
        }

        // Return the result from the readSubmission action
        return $this->readSubmission($request, $submission);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Returns a `submission` field, and `data` field. The submission contains the dynamic form model in a JSON format.
     *
     * @param Request $request
     * @param DynamicFormSubmission $dynamicFormSubmission
     * @return void
     */
    public function readSubmission(Request $request, DynamicFormSubmission $dynamicFormSubmission)
    {
        $liveForm = $dynamicFormSubmission->dynamicForm;

        // Calling data to have it load into dynamicFormSubmission
        $submissionData = $dynamicFormSubmission->data;

        $storedForm = $dynamicFormSubmission->storedDynamicForm();

        return response([
            'submission' => $dynamicFormSubmission,
            'stored_dynamic_form' => $storedForm,
        ]);
    }

    /**
     * Return the submission information through from the requirement history
     *
     * @param Request $request
     * @param RequirementHistory $requirementHistory
     * @return void
     */
    public function readSubmissionByRequirementHistory(Request $request, RequirementHistory $requirementHistory)
    {
        // Getting dynamic form submission from history
        $dynamicFormSubmission = $requirementHistory->dynamicFormSubmission;

        if (!isset($dynamicFormSubmission)) {
            return response([
                'message' => "No submissions were found for that requirement history",
            ], 404);
        }
        return $this->readSubmission($request, $dynamicFormSubmission);
    }

    /**
     * Returns all of the submissions in the system
     * Pagination will be done on the server side
     *
     * @param Request $request
     * @return array<DynamicFormSubmission>
     */
    public function getAllSubmissions(Request $request, DynamicForm $dynamicForm)
    {
        $submissions = $dynamicForm->submissions;
        return response([
            'submissions' => $submissions,
        ]);
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

    /**
     * Takes in a DynamicFormSubmission object and deletes it.
     * Returns a response with a `deleted` field, otherwise returns a 404.
     *
     * @param Request $request
     * @param DynamicFormSubmission $dynamicFormSubmission
     * @return Response
     */
    public function deleteSubmission(Request $request, DynamicFormSubmission $dynamicFormSubmission)
    {
        $deleteCount = $dynamicFormSubmission->delete();
        return response([
            'deleted' => $deleteCount,
        ]);
    }

    // ======================================================================= //

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
     * Finds the user from the request, and returns the role
     * TODO: Change this to return just the role; not just the ID
     */
    protected function getCurrentRoleIDFromRequest($request)
    {
        $user = $request->user();
        if ($user == null) {
            throw new Exception("User was null from request");
        }

        return $user->role->id;
    }

    /**
     * Takes in a data array from a Request, and sets the model properties.
     * Properties that are set are based on the data that is passed in
     *
     * @param array or object $data
     * @param Model $model
     * @return void
     */
    protected function loadRequestToModel($data, $model)
    {
        // Loading object keys
        if (isset($data)) {
            foreach ($data as $propKey => $value) {
                if (isset($model[$propKey])) {
                    $model[$propKey] = $data[$propKey];
                }
            }
        }

        return $model;
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

                // Updating submission data
                $sub_data = DynamicFormSubmissionData::updateOrCreate([
                    'dynamic_form_column_id' => $column->id,
                    'dynamic_form_submission_id' => $submission->id
                ], [
                    'value' => $transformationRes
                ]);

                array_push($transformRes, $sub_data);
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
        $submissionData = DB::table('dynamic_form_submission_data')
            ->where('dynamic_form_column_id', $transformation_query)
            ->where('dynamic_form_submission_id', $submission->id)
            ->first();

        // Error checking - making sure the data is not null
        if (!isset($submissionData)) {
            throw new Exception(__METHOD__ . ": Submission data was null for submission id of " . $submission->id . ", and query: " . $transformation_query);
            return null;
        }

        $submissionDataVal = $submissionData->value;

        return $submissionDataVal;
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
        if (!isset($submission) || !isset($submission->id)) {
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
}
