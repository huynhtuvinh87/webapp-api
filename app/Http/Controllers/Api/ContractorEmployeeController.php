<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractorEmployeeRequest;
use App\Http\Requests\UpdateContractorEmployeeRequest;
use App\Jobs\Notifications\SendEmployeeNewPosition;
use App\Models\Facility;
use App\Models\Position;
use App\Models\Resource;
use Illuminate\Support\Facades\Cache;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Registration\NewEmployee;
use App\Traits\AutoAssignTrait;
use App\Traits\CacheTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Log;



/**
 * Controller for Contractor admins to manage contractor employees
 *
 * Class ContractorEmployeeController
 * @package App\Http\Controllers\Api
 */
class ContractorEmployeeController extends Controller
{
    use CacheTrait;
	use AutoAssignTrait;

    public function index(Request $request)
    {

        if ($request->query('hiring_organization_id')){

            $users = DB::table('users')
                ->select(DB::raw('users.id as user_id,
                    roles.id as role_id,
                    users.email,
                    users.first_name,
                    users.last_name,
                    users.phone_number,
                    users.last_login,
                    users.created_at,
                    users.email_verified_at,
                    (IF (users.password IS NOT NULL, true, false)) as has_password,
                    (ROUND(view_employee_compliance_by_hiring_org.requirements_completed_count / view_employee_compliance_by_hiring_org.requirement_count * 100, 0)) as compliance'
                ))
                ->join('roles', 'users.id', '=', 'roles.user_id')
                ->leftJoin('view_employee_compliance_by_hiring_org', 'users.id', '=', 'view_employee_compliance_by_hiring_org.user_id')
                ->where('roles.entity_key', $request->user()->role->entity_key)
                ->where('roles.entity_id', $request->user()->role->entity_id)
                ->where('roles.role', 'employee')
                ->where('view_employee_compliance_by_hiring_org.hiring_organization_id', $request->query('hiring_organization_id'))
                ->whereNull('roles.deleted_at')
                ->get();

            return response($users);

        }

        $users = DB::table('users')
            ->select(DB::raw('users.id as user_id,
            roles.id as role_id,
            users.email,
            users.first_name,
            users.last_name,
            users.phone_number,
            users.last_login,
            users.created_at,
            users.email_verified_at,
            (IF (users.password IS NOT NULL, true, false)) as has_password,
            (ROUND(view_employee_overall_compliance.requirements_completed_count / view_employee_overall_compliance.requirement_count * 100, 0)) as compliance'))
            ->join('roles', 'users.id', '=', 'roles.user_id')
            ->leftJoin('view_employee_overall_compliance', 'users.id', '=', 'view_employee_overall_compliance.user_id')
            ->where('roles.entity_key', $request->user()->role->entity_key)
            ->where('roles.entity_id', $request->user()->role->entity_id)
            ->where('roles.role', 'employee')
            ->whereNull('roles.deleted_at')
            ->get();

        return response($users);

    }

    public function store(StoreContractorEmployeeRequest $request)
    {

        $exists = false;
        $isOwner = false;
        $position = null;
        $facility = null;
        if ($request->has('position_id')){
            $position = Position::find($request->get('position_id'));

//            Why?
//            if(!DB::table('contractor_position')
//                ->where('contractor_id', $request->user()->role->entity_id)
//                ->where('position_id', $position->id)
//                ->exists()){
//
//                $position = null;
//
//            }

        }

        if ($request->has('facility_id')){

            $facility = Facility::find($request->get('facility_id'));

            if(!DB::table('contractor_facility')
                ->where('contractor_id', $request->user()->role->entity_id)
                ->where('facility_id', $facility->id)
                ->exists()){

                $facility = null;
            }
        }

        //If user exists, fetch it
        $user = User::where('email', $request->get('email'))->first();

        //If user doesn't exist, create
        if (!$user) {
            $exists = true;
            if($request->get('password')){
                $request->merge(["password" => bcrypt($request->get('password'))]);
            }

            $user = User::create($request->all());
        } //If user exists with employee role for this contracting organization, 409, user already exists
        else if ($user->roles()->where('entity_id', $request->user()->role->entity_id)->where('entity_key', $request->user()->role->entity_key)->where('role', 'employee')->first()) {
            return response(["message" => "User already exists"], 409);
        }
        else if ($user->roles()->where('entity_id', $request->user()->role->entity_id)->where('entity_key', $request->user()->role->entity_key)->where('role', 'owner')->first()) {
            $isOwner = true;
        }
        else if ($role = $user->roles()->withTrashed()->where('entity_id', $request->user()->role->entity_id)->where('entity_key', $request->user()->role->entity_key)->where('role', 'employee')->first()){
            $role->restore();
            //$position = $user->assignEmployeePosition($request, $role);
            if ($position){
                $role->positions()->sync($request->get('position_id'), false);
			}
            if ($facility) {
                $role->facilities()->sync($request->get('facility_id'), false);
				$this->autoAssignByEmployee($role, $facility, null, true);
            }
            return response([
                "user" => $user,
                "position" => $position ?? null,
                "facility" => $facility ?? null
            ]);
        }

        //create user
        $role = Role::create([
            "user_id" => $user->id,
            "entity_key" => "contractor",
            "entity_id" => $request->user()->role->entity_id,
            "role" => "employee"
        ]);

        //Assign position if exists in request
        if ($position){
            $role->positions()->sync($request->get('position_id'), false);
        }

		//Assign facility if exists in request
		if ($facility){
			$role->facilities()->sync($request->get('facility_id'), false);
			$this->autoAssignByEmployee($role, $facility, null, true);
		}

        // send welcome email to new employee
        if(!$isOwner){
            $user->notify(new NewEmployee($request->user()));
        }

        if ($exists){
            return response([
                "user" => $user,
                "position" => $position ?? null,
                "facility" => $facility ?? null,
                "message" => __('auth.role_creation')
            ], 202);
        }

        return response([
            "user" => $user,
            "position" => $position ?? null,
			"facility" => $facility ?? null
        ]);

    }

    /**
     * Update user parameters
     * DEPRECATED AND DISABLeD FOR SECURITY
     * @param UpdateContractorEmployeeRequest $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(UpdateContractorEmployeeRequest $request, User $user)
    {

        //update password if in request
        if ($request->has('password')) {
            $request->merge(["password" => bcrypt($request->get('password'))]);
        }

        $user->update($request->all());

        return response($user);

    }

    /**
     * Assign a new position to a user
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function assignPosition(Request $request, User $user)
    {

        $this->validate($request, [
            "position_id" => "required|exists:positions,id"
        ]);


        $role = $user->roles()->where('entity_id', $request->user()->role->entity_id)->where('entity_key', $request->user()->role->entity_key)->where('role', 'employee')->firstOrFail();
        if(!$role || is_null($role)){
            throw new Exception("Role not found, position not assigned");
        }

        $position = Position::find($request->get('position_id'));
        if(!$position || is_null($position)){
            throw new Exception("Position not found, position not assigned");
        }

        $role->positions()->sync($position->id, false);

        // Send notification to employee
        SendEmployeeNewPosition::dispatch($user, $position);

        return response("Position Added to Employee", 200);

    }

    public function unassignPosition(Request $request, User $user){

        $this->validate($request, [
            "position_id" => "required|exists:positions,id"
        ]);

        //get appropriate role
        $role = $user->roles()->where('entity_id', $request->user()->role->entity_id)->where('entity_key', $request->user()->role->entity_key)->where('role', 'employee')->first();

        $role->positions()->detach($request->get('position_id'));

        return response(["Position Removed"], 200);

	}

    /**
     * Get users' positions
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function positions(Request $request, User $user)
    {

        $role = $user->roles()->where('entity_id', $request->user()->role->entity_id)->where('entity_key', $request->user()->role->entity_key)->where('role', 'employee')->first();

        return response($role->positions);
	}

    public function facilities(Request $request, User $user)
    {
		$role = $user->roles()
			->where('entity_id', $request->user()->role->entity_id)
			->where('entity_key', $request->user()->role->entity_key)
			->where('role', 'employee')->first();

        return response($role->facilities);
	}

	public function assignFacility(Request $request, User $user){
		// Validating facility_id exists
        $this->validate($request, [
            "facility_id" => "required|exists:facilities,id"
		]);

        //get appropriate role
		$role = $user->roles()->where('entity_id', $request->user()->role->entity_id)->where('entity_key', $request->user()->role->entity_key)->where('role', 'employee')->first();

		$facility = Facility::find($request->get('facility_id'));

		$contractorFacilityRelationExists = DB::table('contractor_facility')
			->where('facility_id', $facility->id)
			->where('contractor_id', $request->user()->role->entity_id)
			->exists();

		if($contractorFacilityRelationExists){
			$role->facilities()->sync($facility->id, false);
			$this->autoAssignByEmployee($role, $facility, null, true);
            return response("Facility Added");
		}

        return response(["message" => "Could not assign facility"], 409);
	}

	public function unassignFacility(Request $request, User $user){
        $this->validate($request, [
            "facility_id" => "required|exists:facilities,id"
        ]);

        //get appropriate role
		$role = $user->roles()
			->where('entity_id', $request->user()->role->entity_id)
			->where('entity_key', $request->user()->role->entity_key)
			->where('role', 'employee')->first();

        $role->facilities()->detach($request->get('facility_id'));

        return response(["message" => "Facility Removed"], 200);
	}

    /**
     * Destroy user role*
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function destroy(Request $request, User $user)
    {

        Role::where('entity_id', $request->user()->role->entity_id)
            ->where('entity_key', $request->user()->role->entity_key)
            ->where('user_id', $user->id)
            ->where('role', 'employee')
            ->delete();

        return response(["message" => "ok"], 200);

    }

    public function sendVerificationEmail(Request $request, User $user){
        Cache::tags($this->getUserCacheTag($user))->flush();
        $user->notify(new NewEmployee($request->user()));
    }

    public function resource(Role $role)
    {
        return response($role->resources);
    }

    public function assignResource(Request $request, Role $role)
    {
        $this->validate($request, [
            "resource_id" => "required|exists:resources,id"
        ]);

        $resource = Resource::find($request->get('resource_id'));
        if (!$resource || is_null($resource)) {
            throw new Exception("Resource not found, employee not assigned");
        }

        $resource->roles()->sync([$role->id]);

        return response(["message" => "Resource added to employee"], 200);
    }
}
