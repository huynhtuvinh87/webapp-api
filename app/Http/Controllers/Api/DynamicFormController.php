<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DynamicForm;
use App\Models\DynamicFormColumn;
use App\Models\DynamicFormSubmissionAction;
use App\Models\HiringOrganization;
use App\Models\Requirement;
use App\Models\Contractor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles the data for the dynamic form
 */
class DynamicFormController extends Controller
{

    public function getForms(Request $request)
    {
        // Getting current user's hiring org
        $hiringOrgId = $request->user()->role->company->id;

        // Requirement that has form for Rating attached to it
        $requirement_attached_form = HiringOrganization::where('id', $hiringOrgId)->pluck('form_rating_requirement_id');

        // Returning forms only relative to the same hiring org
        $forms_query = DynamicForm::where('hiring_organization_id', $hiringOrgId);

        // if (isset($requirement_attached_form)) {
        //     // Form used for rating
        //     $count = Requirement::find($requirement_attached_form)->count();

        //     // Excludes form used for rating
        //     if ($count) {
        //         $form_used_for_rating = Requirement::find($requirement_attached_form)->pluck('integration_resource_id');

        //         $forms_query = $forms_query->where('id', '!=', $form_used_for_rating);
        //     }
        // }

        $forms = $forms_query->get()->toArray();

        return response(["forms" => $forms]);
    }

    // =============================== //
    // ==================== Form CRUD API Functions ==================== //
    // =============================== //

    /**
     * Creates a new form based on the form and columns data in the POST request
     * request should contain a `form` and `columns` property structured identically to readForm
     *
     * @param Request $request
     * @param DynamicForm $form
     * @return void
     */
    public function createForm(Request $request)
    {

        // Getting dynamic form from request
        $form = $this->createFormFromRequest($request);
        // Saving form
        $form->save();

        // Get form ID
        $formId = $form->id;

        // Getting columns from request
        try {
            $columns = $this->createColumnsFromRequest($request, $formId);
            // Going through columns and saving each one
            foreach ($columns as $column) {
                $column->save();
            }
        } catch (Exception $e) {
            $columns = [];
        }

        try {
            $actions = $this->createActionsFromRequest($request, $form);
            foreach ($actions as $action) {
                $action->save();
            }
        } catch (Exception $e) {
            $actions = [];
        }

        return $this->readForm($request, $form);
    }

    /**
     * Create form columns right away, so we can use the newly created column id in transformation
     *
     * @param DynamicForm $dynamicForm
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws Exception
     */
    public function createColumn(Request $request, DynamicForm $dynamicForm)
    {
		// Error checking for dynamic form
        if (!isset($dynamicForm)) {
            throw new Exception("dynamic form was not found!");
        }
        if (!isset($dynamicForm->id)) {
            throw new Exception("ID was not found for the dynamic form. Error retrieving data.");
		}

		// Error checking for column
		if(!$request->has('column')){
			throw new Exception('Column was not passed to server');
		}
		$newColumn = $request->get('column');

		$columnLabel = $newColumn['label'];
		$columnDescription = isset($newColumn['description']) && $newColumn['description'] != '' ? $newColumn['description'] : null;
        $columnType = $newColumn['type'];
		$columnTransformation = null;
        if ($request->has('transformation')) {
            $columnTransformation = $newColumn['transformation'];
        }
        $columnFileId = ($request->has('file_id')) ? $newColumn['file_id'] : null;
        $columnData = ($request->has('data')) ? $newColumn['data'] : null;
		if(!isset($columnLabel)){
			throw new Exception("Column label was not defined");
		}

        // Returning data
        $response = DynamicFormColumn::create([
            'dynamic_form_id' => $dynamicForm->id,
            'label' => $columnLabel,
            'description' => $columnDescription,
            'transformation' => $columnTransformation,
            'file_id' => $columnFileId,
            'data' => $columnData,
            'type' => $columnType
        ]);
        
        return response($response);
    }

    /**
     * Retrieves the specified form and columns
     *
     * @param Request $request
     * @param DynamicForm $form
     */
    public function readForm(Request $request, DynamicForm $dynamicForm)
    {
        try {

            if (!isset($dynamicForm)) {
                throw new Exception("dynamic form was not found!");
            }
            if (!isset($dynamicForm->id)) {
                throw new Exception("ID was not found for the dynamic form. Error retrieving data.");
            }
            if (!isset($dynamicForm->title)) {
                throw new Exception("Title was not set in the form for " . $dynamicForm->id);
            }

            // determining if requester is a contractor
            $isContractor = get_class($request->user()->role->company) == Contractor::class;

            // Returning data
            $responseData = $dynamicForm->getResponseModel(!$isContractor);
            return response($responseData);

        } catch (Exception $e){
            return response(['message' => $e->getMessage()], 400);
        }
    }

    public function readFormByRequirement(Request $request, Requirement $requirement)
    {
        if (!isset($requirement)) {
            throw new Exception("Requirement was not found!");
        }

        $requirementId = $requirement['id'];

        // Getting dynamic form from requirement
        $dynamicFormId = $requirement['integration_resource_id'];
        if (!isset($dynamicFormId)) {
            throw new Exception("Requirement $requirementId does not have a dynamic form, or the form $dynamicFormId does not exist");
        }

        $dynamicForm = $requirement->integrationResource;

        if (!isset($dynamicForm)) {
            throw new Exception("Dynamic form ($requirement->integration_resource_id) could not be found!");
        }

        return $this->readForm($request, $dynamicForm);
    }

    /**
     * Saves a pre-existing form
     *
     * @param Request $request
     * @param Int $formId
     * @param DynamicForm $form
     * @return void
     */
    public function updateForm(Request $request, DynamicForm $originalForm)
    {
        // Getting original columns and actions
        $originalColumns = $originalForm->columns;
        $originalActions = $originalForm->actions;
        $formId = $originalForm['id'];

        // Getting form information
        $newForm = $request->get('form');
        $newColumns = $newForm['columns'];
        $newActions = $newForm['actions'];

        // TODO: Verify form information before trying to update
        try {
            DB::beginTransaction();

            // Error handling
            if (!isset($formId) || $formId == 0) {
                throw new Exception("Form ID was not set properly!");
            }
            // Making sure original columns is defined
            if (!isset($originalColumns)) {
                throw new Exception("Original columns is empty! \n\n" . json_encode($originalColumns));
            }

            if (!isset($newForm)) {
                throw new Exception("New form was not passed in properly!");
            }

            // Making sure new columns is defined
            if (!isset($newColumns)) {
                throw new Exception("New columns is empty! \n\n" . json_encode($newForm));
            }

            // Creating new columns
            foreach ($newColumns as $newColumn) {
                $newColumn['dynamic_form_id'] = $formId;
                $newColumnObj = DynamicFormColumn::find($newColumn['id']);
                $newColumnObj->update($newColumn);
            }

            // Deleting all actions
            foreach ($originalActions as $original) {
                $original->delete();
            }

            // Creating new actions
            foreach ($newActions as $newAction) {
                $newAction['dynamic_form_id'] = $formId;
                if ($newAction['value'] == '') {
                    $newAction['value'] = '{}';
                }
                $newActionObj = DynamicFormSubmissionAction::create($newAction);
                $newActionObj->save();
            }

            // Updating form
            $updatedForm = DynamicForm::where('id', $formId)->get()->first();

            if (!isset($newForm)) {
                throw new Exception("form was not defined!");
            }

            foreach ($newForm as $key => $newVal) {
                if ($key != 'columns' && $key != 'actions') {
                    $updatedForm[$key] = $newVal;
                }
            }

            $updatedForm->save();

            DB::commit();

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        // Getting results that are stored in the database
        $dbForm = with(new DynamicForm)::where('id', $originalForm->id)->first();

        // Returning the same results as readForm
        return $this->readForm($request, $dbForm);
    }

    /**
     * Takes in a form id and deletes it from the database
     * Returns the number of forms and columns deleted
     * NOTE: Does not delete submission data - separate request
     *
     * @param Request $request
     * @param Int $formId
     */
    public function deleteForm(Request $request, DynamicForm $dynamicForm)
    {
        $deleteCounts = [
            'data' => 0,
            'submissions' => 0,
            'actions' => 0,
            'columns' => 0,
            'forms' => 0,
        ];

        $submissions = $dynamicForm->submissions;

        // Deleting Data
        foreach ($submissions as $submission) {
            $data = $submission->data;
            foreach ($data as $datagram) {
                $datagram->delete();
                $deleteCounts['data'] += 1;
            }

            $submission->delete();
            $deleteCounts['submissions'] += 1;
        }

        foreach ($dynamicForm->actions as $action) {
            $action->delete();
            $deleteCounts['actions'] += 1;
        }

        foreach ($dynamicForm->columns as $column) {
            $column->delete();
            $deleteCounts['columns'] += 1;
        }

        $dynamicForm->delete();
        $deleteCounts['forms'] += 1;

        return response($deleteCounts);
    }

    // =============================== //
    // HELPER FUNCTIONS //
    // =============================== //

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
     * Creates and returns a new DynamicForm object from a request
     * NOTE: form still needs to be saved
     *
     * @param Request $request
     * @return DynamicForm
     */
    protected function createFormFromRequest(Request $request): DynamicForm
    {
        // TODO: Don't allow users to pass in create_role_id and create the form request - retrieve the user using the getCurrentRoleIDFromRequest function
        $requestForm = $request->get('form');
        if (gettype($requestForm) == 'NULL') {
            // Getting created user role
            $createUser = $request->user();
            $createRoleId = null;

            if ($createUser && $createUser->role && $createUser->role->id) {
                $createRoleId = $createUser->role->id;
            }

            if ($createRoleId == null) {
                throw new Exception("createRoleId was null - user (" . $createUser->id . ") may not have a role");
            }

            // Getting organization id
            $hiringOrgId = $requestForm['hiring_organization_id'];
            // If hiring org ID is null, try checking the users role
            if (!isset($hiringOrgId)) {
                $hiringOrgId = $createUser->role->company->id;
            }
            // Validation: Making sure Hiring Org ID was set
            if (!isset($hiringOrgId)) {
                throw new Exception("Hiring Org ID was null");
            }

            $requestForm = [
                'title' => "New Form",
                'description' => "",
                'create_role_id' => $createRoleId,
                'hiring_organization_id' => $hiringOrgId,
            ];
        }

        if (gettype($requestForm) != 'array' && gettype($requestForm) != 'NULL') {
            throw new Exception("\"form\" from request was not an array, is of type " . gettype($requestForm));
        }

        // Validate fields in Request Form
        if (!isset($requestForm['title'])) {
            throw new Exception("No title was defined for the form");
        }
        if (!isset($requestForm['hiring_organization_id'])) {
            Log::info($requestForm);
            throw new Exception("No hiring organization ID was set");
        }

        return DynamicForm::create($requestForm);
    }

    /**
     * Creates and returns a new DynamicFormColumn object from a request
     * NOTE: form still needs to be saved
     *
     * @param Request $request
     * @return DynamicFormColumn[]
     */
    protected function createColumnsFromRequest(Request $request, Int $formId): array
    {

        // ===== Variables ===== //
        $requestColumns = $request->get('columns');
        $returnColumns = []; // Array for the new columns

        // ===== Error checking ===== //

        // Checking to see if the columns are set
        if (!isset($requestColumns)) {
            throw new Exception("columns was not defined properly!");
        }

        // ===== Building Column array ===== //
        // For each of the request columns, create a new DynamicFormColumn and add all of the data to the column, then push the new column object to the returnColumns array.
        foreach ($requestColumns as $requestColumn) {
            // Adding the form ID to the column object (if the formId is set)
            if (isset($formId)) {
                $requestColumn['dynamic_form_id'] = $formId;
            }

            if ($requestColumn['type'] == 'transformation') {
                // Validate transformation

                $jsonTest = json_decode($requestColumn['transformation']);
            }

            $newColumn = DynamicFormColumn::create($requestColumn);

            // Pushing the new column to the column array to be returned
            array_push($returnColumns, $newColumn);
        }

        // ===== Returning Data ===== //
        // Returning the new columns all filled out by the request data
        return $returnColumns;
    }

    /**
     * Creates DynamicFormSubmissionAction's based on the request, and given form
     *
     * @param Request $request
     * @param DynamicForm $form
     * @return array
     */
    protected function createActionsFromRequest(Request $request, DynamicForm $form): array
    {
        // Variables
        $formId = $form->id;
        $requestActions = $request->get('actions');
        $returnActions = [];

        // Skipping foreach loop if no actions a reset
        if (isset($requestActions) && sizeof($requestActions) > 0) {
            // For each action in the request object...
            foreach ($requestActions as $requestAction) {

                // Setting the reference from the action to the form ID
                if (isset($formId)) {
                    $requestAction['dynamic_form_id'] = $formId;
                }

                // Setting column_id for action based on column label
                // Getting column reference by label
                $label = $requestAction['dynamic_form_column_label'];
                $columnId = $form->columns
                    ->where('label', $label)
                    ->first()
                    ->id;

                // Setting column_id
                $requestAction['dynamic_form_column_id'] = $columnId;

                // Create submission action object and add it to return array
                $newAction = DynamicFormSubmissionAction::create($requestAction);
                array_push($returnActions, $newAction);
            }
        }

        return $returnActions;
    }

    /**
     * Deletes the form with the given form id
     *
     * @param Int $formId
     * @return Int Number of forms deleted. Should be 1
     */
    protected function deleteDynamicFormTable(Int $formId)
    {
        // Getting table and field name
        $dynamicFormTableName = with(new DynamicForm)->getTable();
        $dynamicFormTablePK = with(new DynamicForm)->getKeyName();

        // Logic to check to see if variables are valid
        $formTableNameIsSet = $dynamicFormTableName != null && $dynamicFormTableName != '';
        $formPKIsSet = $dynamicFormTablePK != null && $dynamicFormTablePK != '';

        // Error handling: Making sure the Dynamic Form table name and PK is set
        if (!$formTableNameIsSet || !$formPKIsSet) {
            throw new Exception("Table Name / PK are not set for the Dynamic Form table. Table: \"" . $dynamicFormTableName . "\", PK: \"" . $dynamicFormTablePK . "\"");
        }

        // Deleting the table
        $deleteCount = DynamicForm::where($dynamicFormTableName . '.' . $dynamicFormTablePK, $formId)
            ->delete();

        return $deleteCount;
    }

    /**
     * Deletes all of the columns associated with the passed in form ID
     *
     * @param Int $formId
     * @return Int Number of columns deleted
     */
    protected function deleteDynamicFormColumns(Int $formId)
    {
        // Getting table and field names
        $dynamicFormColumnTableName = with(new DynamicFormColumn)->getTable();
        $dynamicFormColumnFK = 'dynamic_form_id';

        // Logic to check to see if the primary keys and table names are set
        $columnTableNameIsSet = $dynamicFormColumnTableName != null && $dynamicFormColumnTableName != '';
        $columnFKIsSet = $dynamicFormColumnFK != null && $dynamicFormColumnFK != '';

        // Error handling: Making sure the Dynamic Form Column table name and PK is set
        if (!$columnTableNameIsSet || !$columnFKIsSet) {
            throw new Exception("Table Name / PK are not set for the Dynamic Form Column table. Table: \"" . $dynamicFormColumnTableName . "\", PK: \"" . $dynamicFormColumnFK . "\"");
        }

        // Deleting all of the columns
        $deleteCount = DynamicFormColumn::where($dynamicFormColumnTableName . '.' . $dynamicFormColumnFK, $formId)
            ->delete();

        return $deleteCount;
    }

    protected function getCurrentRoleIDFromRequest($request)
    {
        // TODO: Set the modify_author_id to be the user ID
        $user = $request->user();
        return 1;
    }
}
