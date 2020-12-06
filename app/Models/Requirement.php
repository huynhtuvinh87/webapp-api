<?php

namespace App\Models;

use App\Models\RequirementContent;
use App\Models\Test;
use App\Models\HiringOrganization;
use App\Models\DynamicForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ExclusionRequest;

class Requirement extends Model
{

    protected $fillable =[
        'type',
        'warning_period',
        'renewal_period',
        'integration_resource_id',
        'count_if_not_approved',
        'notification_email',
        'content_type',
        'description',
        'hard_deadline_date'
    ];

    protected $casts = [
        'warning_period' => 'integer',
        'renewal_period' => 'integer',
        'count_if_not_approved' => 'integer'
    ];
    /**
     * Array of requirement types
     *
     * @var array
     */
    private $types = [
        'upload',
        'upload_date',
        'review',
        'test',
        'upload_internal',
        'form'
    ];

    protected $requirementTypes = [
        'upload',
        'upload_date',
        'review',
        'test',
        'internal',
    ];

    protected $requirementContentTypes = [
        'file',
        'text',
		'url',
		'none'
    ];

    /**
     * Getter function for the types array
     *
     * @return void
     */
    public function getTypes()
    {
        return $this->requirementTypes;
    }

    /**
     * Getter function for content types array
     *
     * @return void
     */
    public function getContentTypes()
    {
        return $this->requirementContentTypes;
    }

    public function departments(){
        return $this->belongsToMany(Department::class);
    }

    public function matchingDepartments(Role $role)
    {
        // Owners get full access
        if($role->role == 'owner'){
            return true;
        }

        // Getting requirements attached to this department
        $requirementDepartments = $this->departments;
        // NOTE: If requirement doesn't have any departments, white list
        if($requirementDepartments->count() == 0){
            return true;
        }

        // Getting departments assigned to role
        $roleDepartments = $role->departments;
        // NOTE: If the role doesn't have any departments, white list
        if($roleDepartments->count() == 0){
            return true;
        }

        // determining a list of matching departments
        $matchingDepartments = $roleDepartments
            ->intersect($requirementDepartments);
        $hasMatchingDepartments = $matchingDepartments->count() > 0;

        return $hasMatchingDepartments;
    }

    public function positions(){
        return $this->belongsToMany(Position::class)->withTimestamps();
    }

    public function history(){
        return $this->hasMany(RequirementHistory::class);
    }

    public function content(){
        return $this->hasMany(RequirementContent::class);
    }

    public function exclusionRequest(){
        return $this->hasMany(ExclusionRequest::class);
    }

    public function getContent(){
        return $this->content()->get();
    }

    public function loadContents(){
        $this->contents = $this->getContent();
    }

    public function localizedContent(){
        $lang = App::getLocale();

        $preference = $this->content()->where('lang', $lang);

		// If content has preference, return preferred content
        if ($preference->exists()){
            return $preference->first();
        }

		// If users language is not english...
        if ($lang !== 'en'){
            $secondary = $this->content()->where('lang', $lang);

            if ($secondary->exists()){
                return $secondary->first();
            }
        }

		// QUESTION: Why the fuck is it checking content_type??
        return $this->content()->whereNotNull('id')->first();

    }

    public function loadContent(){
        $this->localizedContent = $this->localizedContent();
    }

    /**
     * Return true if Requirement has a content with the provided language
     * @param $lang
     * @return bool
     */
    public function hasContent($lang){
		// Ignore `none` as there is no column for none.
		if($this->attributes['content_type'] != 'none'){
			$contentExists = $this->content()
			->where('lang', $lang)
			->whereNotNull($this->attributes['content_type'])
			->exists();
		} else {
			return false;
		}
    }

    public function form()
    {
        return $this->integrationResource();
    }

    public function test()
    {
        return $this->integrationResource();
    }

    public function integrationResource(){
        return $this->morphTo('resource', 'type', 'integration_resource_id');
    }

    public function hiringOrganization(){
        return $this->belongsTo(HiringOrganization::class);
    }
}
