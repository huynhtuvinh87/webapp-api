<?php

namespace App\Traits;

use App\Models\DynamicFormSubmission;
use App\Models\DynamicForm;
use App\Models\DynamicFormSubmissionData;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Models\HiringOrganization;
use App\Models\Rating;
use App\Models\Role;
use App\Models\Contractor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;

trait RatingControllerTrait
{
    /**
     * Check to see if the form is assigned through a requirement as a rating form
     */
    public function isRatingForm($form, $hiringOrg){

		// If rating system is not form, can't possibly be a rating form
		$isFormRatingSystem = $hiringOrg->rating_system == 'form';
		if(!$isFormRatingSystem){
			return false;
		}

    	$query = DB::table('hiring_organizations')
    		->select(DB::raw("COUNT(*) as count"))
    		->leftJoin(
    			'requirements',
    			'requirements.id',
    			'hiring_organizations.form_rating_requirement_id'
    		)
    		->leftJoin(
    			'dynamic_forms',
    			'dynamic_forms.id',
    			'requirements.integration_resource_id'
            )
            ->where('hiring_organizations.id', '=', $hiringOrg->id)
            ->where('dynamic_forms.id', '=', $form->id)
            ->whereNotNull('hiring_organizations.form_rating_requirement_id')
    		->first();

    	return $query->count > 0;
    }

    public function ratingCreateFromForm(DynamicFormSubmission $submission, Contractor $contractor, HiringOrganization $hiringOrg, Role $role){
    	try{
	    	$ratingData = [];

	    	if(!isset($submission)){
	    		throw new Exception("Submission was not defined");
	    	}

	    	$formData = $submission['data'];
	    	if(!isset($formData)){
	    		throw new Exception("Form Data was not defined");
	    	}

//	    	$ratingValue = $this->getRatingValueFromSubmission($hiringOrg, $submission);

            $ratingSubmissionData = DynamicFormSubmissionData::where('dynamic_form_submission_id', $submission->id)
                ->where('dynamic_form_column_id', $hiringOrg->dynamic_form_column_id_rating)
                ->first();

            $ratingCommentSubmissionData = DynamicFormSubmissionData::where('dynamic_form_submission_id', $submission->id)
                ->where('dynamic_form_column_id', $hiringOrg->dynamic_form_column_id_rating_comment)
                ->first();


	    	$ratingData = [
	    		'role_id' => $role->id,
	    		'contractor_id' => $contractor->id,
	    		'hiring_organization_id' => $hiringOrg->id,
	    		'rating_system' => $hiringOrg->rating_system,
	    		'dynamic_form_submission_id' => $submission->id,
	    		'rating' => number_format($ratingSubmissionData->value,2),
	    		'comments' => $ratingCommentSubmissionData->value ?? null,
	    	];

	        $rating = Rating::create($ratingData);
	    } catch (Exception $e){
			throw new Exception($e->getMessage() . " - Please contact support for further assistance.");
	    }
    }

    /**
     * Takes the submission object and returns the rating value
     * Column the rating is based on is defined in the hiring org
     */
    public function getRatingValueFromSubmission($hiringOrg, $submission){

    	// Error handling
    	if(!isset($hiringOrg) || !($hiringOrg instanceof HiringOrganization)){
    		throw new Exception("Hiring Org was null / improperly defined");
    	} else if (!isset($submission)){
    		throw new Exception("Submission was not defined");
    	}
    	// Column ID the rating is based on
    	if(!isset($hiringOrg->dynamic_form_column_id_rating) && is_int($hiringOrg->dynamic_form_column_id_rating)){
    		Log::debug($hiringOrg);
    		throw new Exception("Hiring Org has no proper rating requirement ID defined - cannot retrieve rating");
    	}
    	// Get submission data based on column ID
		$submission = $submission->fresh();
    	$submissionData = $submission->data;
		$ratingData = $submissionData
			->filter(function($datagram) use ($hiringOrg){
				if(!is_int($datagram->dynamic_form_column_id)){
					throw new Exception("form column ID was not an integer");
				}
				if(!is_int($hiringOrg->dynamic_form_column_id_rating)){
					throw new Exception("Rating column ID is not an integer");
				}
				return $datagram->dynamic_form_column_id == $hiringOrg->dynamic_form_column_id_rating;
			})
			->values()->all();

    	if(!isset($ratingData) || sizeof($ratingData) == 0 || !isset($ratingData[0])){
    		Log::debug("State", [
    			'submission Data' => $submissionData,
				'Hiring Org' => $hiringOrg,
				'ratingData' => $ratingData
    		]);
    		throw new Exception("Rating information could not be found");
    	}

    	// Return the rating value
    	$value = $ratingData[0]->value;
    	if(!isset($value)){
			Log::error("Value was undefined", [
				'ratingData' => $ratingData[0]
			]);
    		throw new Exception("Rating value could not be determined.");
    	}

    	return $value;
    }
}
