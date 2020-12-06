<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\DynamicForm;
use App\Models\DynamicFormSubmission;
use App\Models\DynamicFormSubmissionData;
use App\Models\Rating;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Models\Role;
use App\Traits\CreateFormSubmissionTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RatingController extends Controller
{
    use CreateFormSubmissionTrait;

    /**
     * @var $role
     * @var $hiring_organization
     * @var $request
     */
    private $role;
    private $hiring_organization;
    private $request;
    private $requirement;

    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            // Request
            $this->request = $request;

            // Get user and user's role
            $this->role = $this->getRole($request);

            // Get Hiring Organization
            $this->hiring_organization = $this->getHiringOrganization();

            if ($this->hiring_organization->rating_system == "form") {
                //Get used requirement
                $this->requirement = $this->getRequirement();

                //Get form used to rate
                $this->form = $this->getForm();
            }

            return $next($request);
        });
    }

    /**
     * @param Request $request
     * @param Contractor $contractor
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(Request $request, Contractor $contractor)
    {
        try {

            $rating_data = [
                'rating' => $this->request->rating ?? null,
                'comments' => ($this->hiring_organization->rating_system == "star") ? $this->request->comments : null,
                'contractor_id' => $contractor->id,
                'hiring_organization_id' => $this->hiring_organization->id,
                'role_id' => $this->role->id,
                'rating_system' => $this->hiring_organization->rating_system,
            ];

            //region complex rating
            if ($this->hiring_organization->rating_system == "form") {

                $requestForm = $this->request->get('form');
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

                // Getting form
                $dynamicForm = DynamicForm::where('id', $this->form->id)->first();

                // Add requirement history entry
                $requirementHistory = RequirementHistory::create([
                    "requirement_id" => $this->hiring_organization->form_rating_requirement_id,
                    "completion_date" => now(),
                    "role_id" => $this->role->id,
                    "contractor_id" => $contractor->id,
                ]);

                // Create submission object
                $submission = $this->createSubmissionFromRequest($this->request, $dynamicForm, $requirementHistory);

                // Creating submission data from request
                foreach ($requestData as $datagram) {
                    if (gettype($datagram) != 'array') {
                        throw new Exception("Datagram was not type of array: " . json_encode($datagram));
                    }
                    $data = new DynamicFormSubmissionData($datagram);
                    $data['dynamic_form_submission_id'] = $submission->id;
                    $data->save();
                }

                // Store submission with the current users ID
                $submission->save();

                // Calling readSubmission to execute transform
                $form_submitted = $this->readSubmission($submission);

                $ratingSubmissionData = DynamicFormSubmissionData::where('dynamic_form_submission_id', $submission->id)
                    ->where('dynamic_form_column_id', $this->hiring_organization->dynamic_form_column_id_rating)
                    ->first();

                $ratingCommentSubmissionData = DynamicFormSubmissionData::where('dynamic_form_submission_id', $submission->id)
                    ->where('dynamic_form_column_id', $this->hiring_organization->dynamic_form_column_id_rating_comment)
                    ->first();

                if (!isset($ratingSubmissionData) || !isset($ratingCommentSubmissionData)) {
                    throw new Exception("Unable to retrieve rating and/or comment value from submission data in db");
                }
                if (!isset($ratingSubmissionData->value)) {
                    throw new Exception("Rating value was null");
                }
                if (!isset($ratingCommentSubmissionData->value)) {
                    throw new Exception("Comment value was null");
                }

                $rating_data['rating'] = number_format($ratingSubmissionData->value,2);
                $rating_data['comments'] = $ratingCommentSubmissionData->value;
                $rating_data['dynamic_form_submission_id'] = $submission->id;

            }
            //endregion

            $rating = Rating::create($rating_data);
            $rating['form'] = $form_submitted ?? null;

            return $this->read($request, $contractor);

        } catch (Exception $e) {

            Log::error($e->getMessage());
            return response([
                'message' => 'There was an error saving the rating',
            ]);

        }
    }

    /**
     * @param Contractor $contractor
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function read(Request $request, Contractor $contractor)
    {
    	try {
			if(!isset($this->hiring_organization)){
				throw new Exception("Hiring Organization not set");
			}

			$ratingSystem = $this->hiring_organization->rating_system;
			if(!isset($ratingSystem)){
				throw new Exception("Rating System was not defined");
			}

			if(!isset($contractor->id)){
				throw new Exception("Contractor was not set");
			}
			if(!isset($this->role->company->id)){
				throw new Exception("Hiring Org ID was not set through role");
			}
			if(!isset($this->hiring_organization->rating_system)){
				throw new Exception("Rating system was not set");
			}

	        // Get rating from contractor
	        // Filter by contractor and hiring org
	        $ratingQuery = Rating::where('contractor_id', $contractor->id)
				->where('hiring_organization_id', $this->role->company->id)
	            ->where('rating_system', $this->hiring_organization->rating_system);

	        // If visibility is private, filter out ratings that are not of the same role
	        if ($this->hiring_organization->rating_visibility == 'private') {
	            $ratingQuery = $ratingQuery->where('role_id', $this->role->id);
			}

			$ratings = $ratingQuery->get();

	        $ratings->map(function ($rating) use ($ratingSystem){
				// Need extra error checking here to ensure that ratingSystem was passed into the map function
				if(!isset($ratingSystem)){
					throw new Exception("Rating System was not defined");
				}

	            if ($ratingSystem == 'form') {
	                // NOTE: submission isn't used anywhere, but calling this loads dynamic form submission into rating
	                $submission = $rating->dynamicFormSubmission;
	                if (isset($submission)) {
		                $form = $submission->storedDynamicForm();
		                $rating['form'] = $form;
		                $readSubmission = $this->readSubmission($submission);

		                $rating['form']['submission'] = $readSubmission['submission'];
		                $rating['form']['stored_dynamic_form'] = $readSubmission['stored_dynamic_form'];
	                } else {
	                	Log::debug(
	                		"Rating system expected a dynamic form, but none was present",
	                		[
	                			'Rating' => $rating
	                		]
		                );
	                }
	            }

				$role = $rating->role;

				// If role is defined, set the reviewer to the user,
				// Else, set to null
				// NOTE: Legacy ratings did not keep track of who created the rating
				if(isset($role)){
	            $reviewerUser = $rating->role->user;
				} else {
					$reviewerUser = null;
				}

	            $rating['reviewer'] = $reviewerUser;

	            return $rating;
	        });

	        return response(['ratings' => $ratings]);
    	} catch(Exception $e){
    		return $this->handleError($e, 500, __METHOD__);
    	}
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    private function getRole(Request $request)
    {

        // Get user and user's role
        $role = $request->user()->role;

        if (!isset($role)) {
            throw new Exception("Role / RoleId was not defined");
        } else {
            return $role;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function getHiringOrganization()
    {

        // Get Hiring Organization based on role
        $hiring_organization = $this->role->company;

        if (!isset($hiring_organization)) {
            throw new Exception("Hiring Organization not defined.");
        } else {
            return $hiring_organization;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function getRequirement()
    {
        //Requirement
        $requirement = Requirement::find($this->hiring_organization->form_rating_requirement_id);
        if (!isset($requirement)) {
            throw new Exception("Rating requirement not found.");
        } else {
            return $requirement;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function getForm()
    {
        //Form Data
        $form = DynamicForm::find($this->requirement->integration_resource_id);
        if (!isset($form)) {
            throw new Exception("Form not found.");
        } else {
            return $form;
        }
    }

    private function handleError($exception, $code, $method){
    	Log::error("Error in " . $method);
    	Log::error($exception->getMessage());
    	return response([
    		'message' => $exception->getMessage()
    	], $code);
    }

}
