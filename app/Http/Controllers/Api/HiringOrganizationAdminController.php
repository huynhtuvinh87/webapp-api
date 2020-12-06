<?php

namespace App\Http\Controllers\Api;

use App\Models\Department;
use App\Models\Facility;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Traits\ErrorHandlingTrait;
use Exception;

class HiringOrganizationAdminController extends Controller
{
    use ErrorHandlingTrait;

    public function index(Request $request){
        $admins = Role::join('users', 'users.id', '=', 'roles.user_id')
            ->select([
                'users.id as user_id',
                'users.email',
                'users.first_name',
                'users.last_name',
                'users.phone_number',
                'roles.access_level',
                'roles.can_invite_contractor',
                'roles.can_create_rating'
            ])
            ->where('roles.entity_key', $request->user()->role->entity_key)
            ->where('roles.entity_id', $request->user()->role->entity_id)
            ->where('roles.role', 'admin')
            ->where('users.id', '!=', $request->user()->id)
            ->get();

        return response(['admins' => $admins]);
    }

    public function show(Request $request, User $user){

        $role = $this->getRole($request, $user);

        $user = Role::join('users', 'users.id', '=', 'roles.user_id')
            ->select([
                'users.id as user_id',
                'users.email',
                'users.first_name',
                'users.last_name',
                'users.phone_number',
                'roles.access_level',
                'roles.id as id',
                'roles.can_invite_contractor',
                'roles.can_create_rating'
            ])->with('facilities', 'departments')->find($role->id);

        return response([ 'user' => $user]);

    }

    public function store(Request $request)
    {

        $this->validate($request, [
            "email" => "required|email",
            "first_name" => "min:2",
            "last_name" => "min:2",
            "password" => "required|confirmed",
            "access_level" => "required|numeric|between:1,3",
            "department_ids" => "array|min:1",
            "department_ids.*" => "numeric",
            "facility_ids" => "array|min:1",
            "facility_ids.*" => "numeric",
            "can_create_rating" => "sometimes|required|boolean",
            "can_invite_contractor" => "sometimes|required|boolean",
        ]);

        //If user exists, fetch it
        $user = User::where('email', $request->get('email'))->first();

        //If user doesn't exist, create
        if (!$user) {

            $request->merge(["password" => bcrypt($request->get('password'))]);

            $user = User::create($request->all());
        } //If user exists with admin role for this contracting organization, 409, user already exists
        else if ($user->roles()->where('entity_id', $request->user()->role->entity_id)->where('entity_key', $request->user()->role->entity_key)->where('role', 'admin')->first()) {
            return response(["message" => "User already exists"], 409);
        }

        //create user
        $role = Role::create([
            "user_id" => $user->id,
            "entity_key" => "hiring_organization",
            "entity_id" => $request->user()->role->entity_id,
            "role" => "admin",
            "access_level" => $request->get('access_level'),
            "can_create_rating" => $request->get('rate_contractor'),
            "can_invite_contractor" => $request->get('invite_contractor'),
        ]);
        // Update the response model with the roles
        $user->can_create_rating = $role->can_create_rating;
        $user->can_invite_contractor = $role->can_invite_contractor;

        if (is_array($request->get('facility_ids'))){
            foreach ($request->get('facility_ids') as $facility_id){
                $role->facilities()->sync($facility_id, false);
            }
        }

        if (is_array($request->get('department_ids'))){
            foreach($request->get('department_ids') as $department_id){
                $role->departments()->sync($department_id, false);
            }
        }

        return response([
            "user" => $user
        ]);
    }

    public function update(Request $request, User $user){

        $this->validate($request, [
            "access_level" => "sometimes|required|numeric|between:1,4",
            "can_create_rating" => "sometimes|required|boolean",
            "can_invite_contractor" => "sometimes|required|boolean",
        ]);

        if (!Role::where('user_id', $user->id)->where('entity_key', 'hiring_organization')->where('entity_id', $request->user()->role->entity_id)->exists()){
            return response('not authorized', 403);
        }

        $role = $this->getRole($request, $user);

        $access_level = $request->get('access_level') ?? null;
        $can_rate = $request->get('can_create_rating') ?? null;
        $can_invite = $request->get('can_invite_contractor') ?? null;

        if(isset($access_level)) {
            $role->access_level = $access_level;
        }
        if(isset($can_rate)) {
            $role->can_create_rating = $can_rate;
        }
        if(isset($can_invite)) {
            $role->can_invite_contractor = $can_invite;
        }

        if(!$role->validate()['isValid']){
            // Returning only the first error message for ease
            $message = "There was an error processing your request. Please try again later.";
            $validation = $role->validate();
            if(
                isset($validation['errors'])
                && isset($validation['errors'][0])
                && isset($validation['errors'][0]['error'])
            ){
                $message = $validation['errors'][0]['error'];
            }
            return $this->errorResponse(new Exception($message), 400);
        }

        $role->save();

        return response(["user" => $user, "role" => $role]);

    }

    public function destroy(Request $request, User $user){

        $role = $this->getRole($request, $user);

        $role->delete();

        return response(["message" => "ok"], 200);
    }

    public function getDepartments(Request $request, User $user){

        $role = $this->getRole($request, $user);

        return response(['departments' => $role->departments]);

    }

    public function addDepartments(Request $request, User $user){

        $this->validate($request, [
            'department_ids' => 'required|array',
            'department_ids.*' => 'numeric'
        ]);

        $role = $this->getRole($request, $user);

        foreach ($request->get('department_ids') as $department){

            if (Department::find($department)->hiring_organization_id !== $request->user()->role->entity_id){
                continue;
            }

            $role->departments()->sync($department, false);
        }

        return response(["message" => "ok"], 200);

    }

    public function removeDepartments(Request $request, User $user){
        $this->validate($request, [
            'department_ids' => 'required|array',
            'department_ids.*' => 'numeric'
        ]);

        $role = $this->getRole($request, $user);

        foreach ($request->get('department_ids') as $department){
            $role->departments()->detach($department);
        }

        return response(["message" => "ok"], 200);

    }

    public function getFacilities(Request $request, User $user){
        $role = $this->getRole($request, $user);

        return response(['facilities' => $role->facilities]);
    }

    public function addFacilities(Request $request, User $user){

        $this->validate($request, [
            'facility_ids' => 'required|array',
            'facility_ids.*' => 'numeric'
        ]);

        $role = $this->getRole($request, $user);

        foreach ($request->get('facility_ids') as $facility){

            if (Facility::find($facility)->hiring_organization_id !== $request->user()->role->entity_id){
                continue;
            }

            $role->facilities()->sync($facility, false);
        }

        return response(["message" => "ok"], 200);

    }

    public function removeFacilities(Request $request, User $user){
        $this->validate($request, [
            'facility_ids' => 'required|array',
            'facility_ids.*' => 'numeric'
        ]);

        $role = $this->getRole($request, $user);

        foreach ($request->get('facility_ids') as $facility){
            $role->facilities()->detach($facility);
        }

        return response(["message" => "ok"], 200);
    }

    /**
     * @param $request
     * @param $user
     * @return Role
     */
    private function getRole($request, $user){
        return $user->roles()->where('entity_key', 'hiring_organization')->where('entity_id', $request->user()->role->entity_id)->where('role', 'admin')->with('user')->firstOrFail();
    }
}
