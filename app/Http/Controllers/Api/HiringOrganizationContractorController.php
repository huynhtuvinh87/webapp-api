<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AssignAutoAssignPositions;
use App\Jobs\Notifications\SendEmployeeNewPosition;
use App\Jobs\Notifications\SendNewRequirementNotification;
use App\Jobs\SystemNotification;
use App\Models\Contractor;
use App\Models\Facility;
use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Rating;
use App\Models\Role;
use App\Models\PositionRole;
use App\Models\User;
use App\Models\Resource;
use App\Models\ResourcePosition;
use App\Notifications\Registration\Invite;
use App\Traits\AutoAssignTrait;
use App\Traits\CacheTrait;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HiringOrganizationContractorController extends Controller
{
	use AutoAssignTrait, NotificationTrait, CacheTrait;

    public function index(Request $request){

		$contractors = $request
			->user()->role->company
			->contractors()
			// ->leftJoin('ratings', function($query) use ($request){
			// 	$query->on('ratings.contractor_id', '=', 'contractors.id')
			// 		->where('ratings.hiring_organization_id', '=', $request->user()->role->entity_id);
			// })
			->leftJoin('roles', function($query) use ($request){
				$query->on('roles.entity_id', '=', 'contractors.id')
					->where('roles.entity_key', '=', 'contractor')
					->where('roles.role', '=', 'owner');
			})
			->leftJoin('users', function($query) use ($request){
				$query->on('roles.user_id', '=', 'users.id');
			})
			->select(
				'contractors.*',
				'users.email',
				'users.phone_number'
			)
			->get();

        return response(['contractors' => $contractors]);
    }

    public function search(Request $request){

        $limit = $request->query('limit', 20);

        $contractorSearchQuery = Contractor::leftJoin('contractor_hiring_organization', function($query) use ($request){
            $query->where('contractor_hiring_organization.hiring_organization_id', '=', $request->user()->role->entity_id)
            ->on('contractors.id', '=', 'contractor_hiring_organization.contractor_id');
        })
            ->leftJoin('roles', 'contractors.owner_id', '=', 'roles.id')
            ->leftJoin('users', 'users.id', '=', 'roles.user_id')
            // Joining Work Types
            ->leftJoin('contractor_work_type', 'contractor_work_type.contractor_id', "=", 'contractors.id')
            ->leftJoin('work_types', 'contractor_work_type.work_type_id', "=", 'work_types.id')
            ->whereNull('contractor_hiring_organization.id')
            ->select(
                'contractors.id as id',
                'users.email as email',
                'users.phone_number as phone_number',
                'contractor_hiring_organization.id as contractor_hiring_organization_id',
                'contractors.name as name',
                'contractors.country as country',
                'contractors.state as state',
                // NOTE: Tried including code; however, this results in duplicates
                // If a contractor has 2+ codes, searching by their name will result in them showing up multiple times
                // Need to append work type after search has been conducted?
                // 'work_types.code as code'
                )
            ->distinct()
            ->limit($limit);

            if($request->has('search')){
                $contractorSearchQuery->where(function($query) use ($request){
                    $query
                        ->where('contractors.name', 'LIKE', '%'.$request->get('search').'%')
                        ->orWhere('work_types.code', "LIKE", "%".$request->get('search')."%");
                });
            }

           return response(['contractors' => $contractorSearchQuery->get()]);

    }

    public function show(Request $request, Contractor $contractor)
    {

        $user = $request->user();

        $facilities = $contractor->facilities()->where('facilities.hiring_organization_id',
        $user->role->entity_id)->get();
        $positions = $contractor->allPositions()->where('positions.hiring_organization_id', $user->role->entity_id)->get();
        $contractor_roles = $contractor->roles()->where('role', 'employee')->get();
        $contractor_employee = new \stdClass();
        $contractor_employees = [];
        $resources = $contractor->resources;
        if($resources) {
            foreach ($resources as $resource) {
                if($resource->roles->first()) {
                    $resource->employee = $resource->roles->first()->user;
                }
            }
        }
        $contractor_resources = [];
        $contractor_owner = $contractor->owner->user;
        $contractor->owner_name = $contractor_owner->first_name.' '.$contractor_owner->last_name;
        $contractor->owner_email = $contractor_owner->email;
        for ($i = 0; $i < count($contractor_roles); $i++) {
            $role = $contractor_roles[$i];             
            $user = User::find($role->user_id);
            $contractor_employee->role_id = $role->id;
            $contractor_employee->email = $user->email;
            $contractor_employee->employee_name = $user->first_name.' '.$user->last_name;
            $contractor_employee->external_id = $contractor_roles[$i]->external_id;
            $contractor_employee->second_external_id = $contractor_roles[$i]->second_external_id;
            array_push($contractor_employees, $contractor_employee);
            $contractor_employee = new \stdClass();
        }
        return response([
            'positions' => $positions,
            'facilities' => $facilities,
            'contractor' => $contractor,
            'resources' => $resources,
            'contractor_employees' => $contractor_employees,
        ]);
    }


    public function deactivate(Request $request, Contractor $contractor){
        $request->user()->role->company->contractors()->detach($contractor);
        
         // remove corporate positions
        $position_ids = $contractor->positions()->where('hiring_organization_id', $request->user()->role->company->id)->pluck('positions.id');
        $contractor->positions()->detach($position_ids);
 
        $hiring_org_id = $request->user()->role->company->id;
        // remove employee positions
        foreach ($contractor->roles as $role) {
            $positionQueryBuilder = DB::table('positions')
                ->join('position_role', 'position_role.position_id', '=', 'positions.id')
                ->join('roles', 'roles.id', '=', 'position_role.role_id')
                ->where('positions.hiring_organization_id', $hiring_org_id)
                ->where('roles.entity_key', 'contractor')
                ->where('roles.entity_id', $role->entity_id)
                ->where('positions.position_type', 'employee')
                ->whereNull('position_role.deleted_at')
                ->get();
            
            $collection = collect($positionQueryBuilder)->map(function ($positionRole) {
                return $positionRole->id;
            });

            DB::table('position_role')
                ->whereIn('id', $collection)
                ->update(['deleted_at' => Carbon::now()]);
        }

        // remove facilities
        $facility_ids = $contractor->facilities()->where('hiring_organization_id', $request->user()->role->company->id)->pluck('facilities.id');
        $contractor->facilities()->detach($facility_ids);

        return response(['message' => 'Deactivated']);
    }

    public function invite(Request $request, Contractor $contractor){

        if(!$request->user()->role->can_invite_contractor){
            return response(['message' => 'You do not have access to invite contractor'], 403);
        }

        if (DB::table('contractor_hiring_organization')
                ->where('contractor_id', $contractor->id)
                ->where('hiring_organization_id', $request->user()->role->entity_id)
                ->exists()){
            return response(['message' => 'Contractor already associated', 'contractor' => $contractor]);
        }


        $request->user()->role->company->contractors()->sync([$contractor->id => ['accepted' => 0]], false);
		$this->autoAssignByContractor($contractor, null, null, true);

        //Ignore exceptions that should be handled outside of main thread (errors will be found in failed_jobs if SystemNotification is malformed)
        try {
            SystemNotification::dispatch(
                //"You've been requested to join ".$request->user()->role->company->name.".",
                "Hi $contractor->name, "
                .$request->user()->role->company->name.
                " has requested that you connect with them on Contractor Compliance.
Please click here to accept this request.",
                Role::where('entity_id', $contractor->id)
                    ->where('entity_key', 'contractor')
                    ->first()->user_id,
                null,
                '/cprofile/?page=hiring-organization', //TODO check path
                'View Invites'
            );

        }
        finally{
            return response(['message' => 'Contractor invited', 'contractor' => $contractor]);
        }

    }

    /**
     * TODO if email/name not unique, suggest to user (402) to ensure that the contractor isn't already in the system
     * TODO email uniqueness should only be for records of contractor owners
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function inviteNew(Request $request){

        if(!$request->user()->role->can_invite_contractor){
            return response(['message' => 'You do not have access to invite contractor'], 403);
        }

        $this->validate($request, [
            'email' => 'required|email',
            'name' => 'required|string',
            'due_date' => 'date|after:now'
		]);

        $token = Str::random(64);
        if(!isset($token) || $token == ''){
            throw new Exception("Unable to generate invite token.");
        }

        DB::beginTransaction();

        try {
			$email = $request->get('email');
			$contractorName = $request->get('name');
			$hiringOrg = $request->user()->role->company;

			Log::info("Sending invite to $email for $hiringOrg->name ($hiringOrg->id)");

			// Check to see if user already exists
			$existingUser = User::where('email', $email)
				->first();

			// If user already exists, use the existing user
			if(isset($existingUser)){
				Log::info("User already exists");
				$user = $existingUser;
			}
			// Else, create a new user
			else {
				Log::info("User does not exist");
				$user = User::create([
					'email' => $request->get('email'),
					'password' => $token
				]);
			}

			// Check to see if the user has a role with a contracting company attached
			$userContractingRoles = $user->roles
				->filter(function($role){
					$contractorCompany = $role
						->company
						->where('roles.entity_key', 'contractor');
					return isset($contractorCompany);
				});

			// if user has a contracting company associated with their accouint
			// Use the first contracting company
			// NOTE: This will cause issues in 0.00001% of the situations where a user has multiple contracting companies
			// Can be handled on a case-by-case basis
			if(sizeof($userContractingRoles) > 0){
				$role = $userContractingRoles[0];
				$contractor = $userContractingRoles[0]->company;
			} else {
				$contractor = Contractor::create([
					'name' => $request->get('name'),
					'owner_id' => $user->id
				]);

				$role = $user->roles()->create([
					'entity_id' => $contractor->id,
					'entity_key' => 'contractor',
					'role' => 'owner'
				]);
			}


            $contractor->owner_id = $role->id;

			$contractor->save();

			$currentContractors = $request->user()->role->company->contractors();

			// Check to see if connection already exists
			$matchingContractors = $currentContractors
				->get()
				->filter(function($currentContractor) use ($contractor){
					return $currentContractor->id == $contractor->id;
				});

			if(sizeof($matchingContractors) == 0){
				$request->user()->role->company->contractors()->attach($contractor, [
					'accepted' => 0,
					'invite_code' => $token,
					'invited_at' => now(),
					'due_date' => $request->get('due_date') ?? null
				]);
			} else {
				return response([
					'message' => "Contractor $email is already connected!"
				], 400);
			}

            DB::commit();

            //Notifying contractor by email
            $user->notify(new Invite($token, $request->user()->role->company->name));

            return response(['contractor' => $contractor, 'invite_code' => $token]);
        }

        catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            return response('An error has occured', 400);
        }
    }

    public function addFacilities(Request $request, Contractor $contractor){

        $this->validate($request, [
            'facility_ids' => 'required|array|min:1',
            'facility_ids.*' => 'numeric'
        ]);

        foreach($request->get('facility_ids') as $facility_id){
            $facility = Facility::find($facility_id);
            if ($facility->hiring_organization_id === $request->user()->role->entity_id){
                $contractor->facilities()->sync($facility_id, false);

                try {
                    AssignAutoAssignPositions::dispatchNow($contractor, $facility);

                }
                catch(\Exception $exception){
                    Log::error($exception);
                }

            }
        }

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response(['message' => 'added']);

    }

    public function removeFacilities(Request $request, Contractor $contractor){
        $this->validate($request, [
            'facility_ids' => 'required|array|min:1',
            'facility_ids.*' => 'numeric'
        ]);

        foreach($request->get('facility_ids') as $facility_id){
            $facility = Facility::find($facility_id);

            if ($facility->hiring_organization_id !== $request->user()->role->entity_id){
                return response(['message' => 'Not Authorized'], 403);
            }

        }

        $contractor->facilities()->detach($request->get('facility_ids'));

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response(['message' => 'removed']);

    }

    /**
     * Get positions assignable to the contractor based on their facilities that are also attached to admin
     * DEPRECATED
     * @param Request $request
     * @param Contractor $contractor
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function assignablePositions(Request $request, Contractor $contractor){

        $user = $request->user();

        if ($user->role->role === 'owner' || !$user->role->facilities()->exists()){

            $facilities = Facility::join('contractor_facility', function($query) use ($contractor){
                $query->on('facilities.id', '=', 'contractor_facility.facility_id')
                    ->where('contractor_facility.contractor_id', '=', $contractor->id);
            })->where('facilities.hiring_organization_id', $request->user()->entity_id)->pluck('facilities.id');

            $positions = Facility::with('positions')->find($facilities)->pluck('positions')->flatten();

            return response(['positions' => $positions]);

        }

        $facilities = Facility::join('contractor_facility', function($query) use ($contractor){
            $query->on('facilities.id', '=', 'contractor_facility.facility_id')
                ->where('contractor_facility.contractor_id', '=', $contractor->id);
        })->join('facility_role', function($query) use ($user){
            $query->on('facilities.id', '=', 'facility_role.facility_id')
                ->where('facility_role.role_id', '=', $user->role->id);
        })->where('facilities.hiring_organization_id', $request->user()->entity_id)->pluck('facilities.id');

        $positions = Facility::with('positions')->find($facilities)->pluck('positions')->flatten();

        return response(['positions' => $positions]);

    }

    /**
     * Get Facilities that are assigned both to the contractor and the current admin
     * DEPRECATED
     * @param Request $request
     * @param Contractor $contractor
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function commonFacilities(Request $request, Contractor $contractor){

        $user = $request->user();

        if ($user->role->role === 'owner'){

            return response(['facilities' => $contractor->facilities]);

        }

        $facilities = Facility::join('contractor_facility', function($query) use ($contractor){
            $query->on('facilities.id', '=', 'contractor_facility.facility_id')
                ->where('contractor_facility.contractor_id', '=', $contractor->id);
        })->join('facility_role', function($query) use ($user){
            $query->on('facilities.id', '=', 'facility_role.facility_id')
                ->where('facility_role.role_id', '=', $user->role->id);
        })->select('facilities.*')->get();

        return response(['facilities' => $facilities]);

    }

    /**
     * Positions assignable to contractor
     * @param Request $request
     * @param Contractor $contractor
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function contractorAssignablePositions(Request $request, Contractor $contractor){

        $facilities = $contractor->facilities()->get()->pluck('id');
        $position_type = ($request->get('type') && $request->get('type') != '') ? $request->get('type') : 'contractor';

        $positions = Position::whereHas('facilities', function($query) use ($facilities){
                    $query->whereIn('facilities.id', $facilities);
                })
                ->where('hiring_organization_id', $request->user()->role->entity_id)
                ->where('position_type', DB::raw("'" . $position_type . "'"))
                ->where('is_active', true)
                ->get();

        return response(['positions' => $positions]);
    }

    public function addPositions(Request $request, Contractor $contractor){

    	try {
	        $this->validate($request, [
	            'position_ids' => 'required|array|min:1',
	            'position_ids.*' => 'numeric'
	        ]);

	        $jobsToProcess = [];
	        $responseMessage = "Added";

	        foreach($request->get('position_ids') as $position_id){

	            $position = Position::find($position_id);
	            $hiringOrg = HiringOrganization::find($position->hiring_organization_id);
	            $contractorOwnerUser = $contractor->owner->user;

	            $positionFromSameHiringOrg = $position->hiring_organization_id === $request->user()->role->entity_id;

	            // If the position can be assigned, assign it and send the notification to the contractor / employee
	            if ($positionFromSameHiringOrg){
		            $contractor->allPositions()->sync($position_id, false);

		            // Sending notification to contractor owner to let them know they have a new position assigned to them
                    $notificationSent = false;
                    if ($this->checkContractorSubscriptionStatus($contractor)){
                        $job = new SendNewRequirementNotification($contractorOwnerUser, $position);
                        $notificationSent = $this->dispatchNow($job);

                        if(!$notificationSent){
                            $responseMessage = "The user was successfully added. The user will not be notified.";
                            Log::warn('Notification was not sent to '.$contractor->name.' when adding position to contractor');
                        }
                    } else {
                        Log::info("Contractor $contractor->name does not have a valid subscription.");
                    }

		        }

	        }

            Cache::tags($this->buildTagsFromRequest($request))->flush();

	        return response(['message' => $responseMessage], 200 );

    	} catch(Exception $e){
    		return response([
    			'message' => $e->getMessage()
    		], 400);
    	}
    }

    /**
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws Exception
     */
    public function addEmployeePositions(Request $request, Role $role)
    {

        if(!$role || is_null($role)){
            throw new Exception("Role not found, position not assigned");
        }

        $positions = Position::find($request->get('position_ids'));
        if(!$positions || is_null($positions)){
            throw new Exception("Position not found, position not assigned");
        }

        foreach ($positions as $position){
            $role->positions()->attach($position->id, ['assigned_by_hiring_organization' => 1]);

            // Send notification to employee
             SendEmployeeNewPosition::dispatch($role->user, $position);
        }

        return response("Position Added to Employee", 200);

    }

    public function removeEmployeePositions(Request $request, Role $role)
    {

        if(!$role || is_null($role)){
            throw new Exception("Role not found, position not removed");
        }

        $positions = Position::find($request->get('position_ids'));
        if(!$positions || is_null($positions)){
            throw new Exception("Position not found, position not removed");
        }

        foreach ($positions as $position){
            $role->positions()->detach($position->id);
        }

        return response("Position Removed from Employee", 200);

    }

       /**
     * Resources assignable to contractor
     * @param Request $request
     * @param Contractor $contractor
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function contractorAssignableResources(Request $request, Contractor $contractor){

        $resources = $contractor->resources()->get();
        $response = response([
            'resources' => $resources
        ]);
        return $response;
    }

    public function addResources(Request $request, Contractor $contractor){
    	try {
            $all = $request->all();
	        $this->validate($request, [
	            'resource_ids' => 'required|array|min:1',
	        ]);

	        $responseMessage = "Added";
	        $notificationsSentProperly = true;

	        foreach($request->get('resource_ids') as $resource_id){

                $resource = Resource::find($resource_id);
                $rp = new ResourcePosition();
                $rp->resource_id = $resource_id;
                $rp->position_id = 1691;
                $rp->save();
	        }

            Cache::tags($this->buildTagsFromRequest($request))->flush();

	        return response(['message' => $responseMessage], $notificationsSentProperly ? 200 : 400);

    	} catch(Exception $e){
    		return response([
    			'message' => $e->getMessage()
    		], 400);
    	}
    }

    public function removeResources(Request $request, Contractor $contractor){
        $this->validate($request, [
            'resource_ids' => 'required|array|min:1',
            //'resource_ids.*' => 'numeric'
        ]);

        foreach($request->get('resource_ids') as $resource_ids){
            $resource = Resource::find($resource_ids);
            $rp = ResourcePosition::where('resource_id', $resource->id);
            $rp->delete();
        }

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response(['message' => 'removed']);

    }

    public function removePositions(Request $request, Contractor $contractor){
        $this->validate($request, [
            'position_ids' => 'required|array|min:1',
            'position_ids.*' => 'numeric'
        ]);

        foreach($request->get('position_ids') as $position_ids){
            $position = Position::find($position_ids);

            if ($position->hiring_organization_id !== $request->user()->role->entity_id){
                return response(['message' => 'Not Authorized'], 403);
            }

        }

        $contractor->allPositions()->detach($request->get('position_ids'));

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response(['message' => 'Position(s) removed'], 200);

    }

    /**
     * Add External ID for a contractor
     * External ID's are used for 3rd party api integrations
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateExternalId(Request $request) {
        try {
            $contractor_id = $request->get('contractor_id');
            $external_id = $request->get('external_id');
            $second_external_id = ($request->has('second_external_id')) ? $request->get('second_external_id') : null;
            Contractor::find($contractor_id)->update(['external_id' => $external_id, 'second_external_id' => $second_external_id]);

            return response(['message' => 'External Id(s) saved!'], 200);
        }
        catch(Exception $ex) {
            Log::debug(__METHOD__, [ 'exception' => $ex->getMessage() ]);
        }    
    }

    /**
     * Add External ID for a contractor's employee's
     * External ID's are used for 3rd party api integrations
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateContractorEmployeeExternalIds(Request $request) {
        try {
            $contractor_employees = $request->get('contractor_employees');
            for ($i = 0; $i < count($contractor_employees); $i++) {
                $contractor_employee = $contractor_employees[$i];
                $role_id = $contractor_employee['role_id'];
                $external_id = $contractor_employee['external_id'];
                $second_external_id = $contractor_employee['second_external_id'];
                $role = Role::find($role_id);
                $role->external_id = $external_id;
                $role->second_external_id = $second_external_id;
                $role->save();
            }

            return response(['message' => 'External Id(s) saved!'], 200);
        }
        catch(Exception $ex) {
            Log::debug(__METHOD__, [ 'exception' => $ex->getMessage() ]);
        }
    }

    /**
     * Add External ID for a contractor's resources
     * External ID's are used for 3rd party api integrations
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateResourceExternalIds(Request $request) {
        try {
            $resources = $request->get('resources');
            for ($i = 0; $i < count($resources); $i++) {
                $current_resource = $resources[$i];
                $external_id = $current_resource['external_id'];

                $resource = Resource::find($current_resource['id']);
                $resource->external_id = $external_id;
                $resource->save();
            }
        } catch(Exception $ex) {
            Log::debug(__METHOD__, [ 'exception' => $ex->getMessage() ]);
        }

        return response(["message" => "External ID Saved"], 200);
    }

    /**
     * Set a rating for a contractor
     * @param Request $request
     * @param Contractor $contractor
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function rate(Request $request, Contractor $contractor){

        $this->validate($request, [
            'rating' => 'required|numeric|min:0|max:5',
            'comments' => 'min:1|string'
        ]);

        $rating = Rating::updateOrCreate([
            'contractor_id' => $contractor->id,
            'hiring_organization_id' => $request->user()->role->entity_id
        ], [
            'rating' => $request->get('rating'),
            'comments' => $request->get('comments')
        ]);

        $rating->save();

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response([
            'rating' => $rating
        ]);
    }

}
