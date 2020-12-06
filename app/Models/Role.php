<?php

namespace App\Models;

use App\Traits\ModelValidationTrait;
use App\ViewModels\ViewEmployeeComplianceByHiringOrg;
use App\ViewModels\ViewEmployeeComplianceByHiringOrgPosition;
use App\ViewModels\ViewEmployeeOverallCompliance;
use App\ViewModels\ViewEmployeeRequirements;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Log;

class Role extends Model
{
    use SoftDeletes;
    use ModelValidationTrait;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        "user_id",
        "role",
        "entity_key",
        "entity_id",
        "access_level",
        "can_create_rating",
        "can_invite_contractor",
        "external_id",
        "second_external_id",
    ];

    protected $valid_access_levels = [
        1, 2, 3, 4,
    ];

    public static $valid_roles = [
        'owner',
        'admin',
        'employee',
    ];

    protected $appends = [
        'show_subcontractor_survey'
    ];

    public function company() : MorphTo
    {
        return $this->morphTo('company', 'entity_key', 'entity_id');
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requirementHistories() : HasMany
    {
        return $this->hasMany(RequirementHistory::class);
    }

    public function resources() : BelongsToMany
    {
        return $this->belongsToMany(Resource::class);
    }

    /**
     * Positions assigned to a role
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function positions() : BelongsToMany
    {

        $hiring_org_ids = $this->company->hiringOrganizations()->pluck('hiring_organizations.id');

        return $this->belongsToMany(Position::class)->whereIn('hiring_organization_id', $hiring_org_ids)->where('positions.is_active', 1)->withPivot('assigned_by_hiring_organization')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function facilities() : BelongsToMany {
        return $this->belongsToMany(Facility::class);
    }

    /**
     * Find positions for HIRING ORGANIZATION ADMIN through facilities. These are the positions assignable to contractors
     * DEPRECATED
     * @return mixed
     */
    public function positionsThroughFacilities(){

        return Position::join('facility_position', 'facility_position.position_id', '=', 'positions.id')->
            join('facilities', 'facilities.id', '=', 'facility_position.facility_id')->
            join('facility_role', 'facility_role.facility_id', '=', 'facilities.id')->
            join('roles', 'roles.id', '=', 'facility_role.role_id')->
            select('positions.*')->
            where('role_id', $this->attributes['id']);

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function departments() : BelongsToMany{
        return $this->belongsToMany(Department::class);
    }

    /**
     * Overall compliance for a user (employee)
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function overallCompliance()
    {
        return $this->hasOne(ViewEmployeeOverallCompliance::class);
    }

    /**
     * Compliance for a user specific to a hiring organizationn(employee)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function complianceByHiringOrganization()
    {
        return $this->hasMany(ViewEmployeeComplianceByHiringOrg::class);
    }

    /**
     * Compliance for a user specific to a position (employee)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function complianceByHiringOrganizationPositions()
    {
        return $this->hasMany(ViewEmployeeComplianceByHiringOrgPosition::class);
    }

    /**
     * Requirements assigned to a user through hiring organization positions (employee)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requirements()
    {
        return $this->hasMany(ViewEmployeeRequirements::class);

    }

    public function moduleVisibility() : MorphMany
    {
        return $this->morphMany('App\Models\ModuleVisibility', 'entity');
    }

    public function subcontractorSurvey(): HasOne
    {
        return $this->hasOne(SubcontractorSurvey::class);
    }

    public function isModuleVisible($moduleId)
    {
        // If module is not set, return null by default
        if(!isset($moduleId)){
            throw new Error("Module ID was undefined");
        }

        $module = Module::where('id', $moduleId)
            ->first();

        // If module is not set, return null by default
        if(!isset($module)){
            return null;
        }

        // Grabbing the specific visibility for the hiring org
        $modVis = $module
            ->moduleVisibility
            ->where('entity_type', 'role')
            ->where('entity_id', $this->id)
            ->first();

        // If the specific visibility is set, set the return value to be the specific visibility
        if(isset($modVis)){
            $visible = $modVis['visible'];
        } else {
            // If specific vis is not set, return generic
            $visible = $module['visible'];
        }

        return $visible;
    }

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
    public function validate()
    {
        /**
         * List of failed validations
         */
        $badValidation = collect([
            $this->validateAccessLevel()
        ]);

        return $this->validateModel($badValidation);
    }

    /**
     * Applies validation to $this->access_level
     *
     * {
     *      isValid: Boolean,
     *      value: any,
     *      error: String
     * }
     *
     * @return Object
     */
    protected function validateAccessLevel()
    {
        // $this->access_level;
        $isValid = collect($this->valid_access_levels)
            ->contains($this->access_level);

        if(!$isValid){
            $error = "'$this->access_level' is not a valid access level.";
        }

        return [
            'value' => $this->access_level,
            'isValid' => $isValid,
            'error' => $error ?? null
        ];
    }

    /**
     * Appends bool whether or not should display subcontractor survey
     *
     * @return void
     */
    public function getShowSubcontractorSurveyAttribute()
    {

        //check if its contractor
        if ($this->entity_key != 'contractor') {
            Log::debug("Not a contractor");
            return false;
        }

        //check if its an owner or admin
        if ($this->role == 'employee') {
            Log::debug("Not an owner or admin");
            return false;
        }

        // check if role is registered after Feb 2020
        $survey_start_date = Carbon::parse(config('api.subcontractor_survey.roles_registered_after'));
        if ($this->created_at < $survey_start_date) {
            Log::debug("Registered before " . $survey_start_date->format("M Y"));
            return false;
        }

        //check if has subcontractors
        if (!$this->company->has_subcontractors) {
            Log::debug("Does not have subcontractors");
            return false;
        }

        //ALC should not see popup.
        $from_restricted_ho = $this->company->hiringOrganizations()->get()->map(function ($hiring_organization) {
            return !in_array($hiring_organization->id, config('api.subcontractor_survey.do_not_survey'));
        })->toArray();

        $filtered_array = array_unique($from_restricted_ho);
        if(count($filtered_array) == 1 && $filtered_array[0] == false){
            Log::debug("Contractor only from restricted HO");
            return false;
        }

        // check if role is at least 7 days old
        if (Carbon::now()->toDateTime() < Carbon::parse($this->created_at)->addDays(config('api.subcontractor_survey.roles_must_be_days_old'))) {
            Log::debug("Role not " . config('api.subcontractor_survey.roles_must_be_days_old') . " days old");
            return false;
        }

        // check if contractor has not been surveyed yet
        $contractor_being_surveyed = SubcontractorSurvey::find($this->entity_id);
        if ($contractor_being_surveyed) {
            Log::debug("Contractor has been surveyed already");
        }

        //check if limit of contractors surveyed has been reached
        if (config('api.subcontractor_survey.limit_contractors') > 0) { // 0 = survey everyone
            $total_contractors_surveyed = SubcontractorSurvey::distinct('entity_id')->count('entity_id');
            if ($total_contractors_surveyed >= config('api.subcontractor_survey.limit_contractors')) {
                Log::debug("Limit of " . config('api.subcontractor_survey.limit_contractors') . " surveyed reached");
                return false;
            }
        }

        // check if role hasn't answered yet
        $count_survey_answer = DB::table('subcontractor_surveys')
            ->join('roles', 'roles.id', '=', 'subcontractor_surveys.role_id')
            ->where('subcontractor_surveys.role_id', $this->id)
            ->where('subcontractor_surveys.entity_key', 'contractor')
            ->where('subcontractor_surveys.entity_id', $this->entity_id)
            ->whereNull('subcontractor_surveys.deleted_at')
            ->count();

        if ($count_survey_answer > 0) {
            Log::debug("Role has answered survey already");
            return false;
        }

        return true;

    }
}
