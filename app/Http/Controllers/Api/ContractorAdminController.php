<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreContractorAdminRequest;
use App\Http\Requests\UpdateContractorAdminRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ContractorAdminController extends Controller
{
    public function index(Request $request)
    {
        $admins = DB::table('roles')
            ->join('users', 'users.id', '=', 'roles.user_id')
            ->select([
                'users.id as user_id',
                'users.email',
                'users.first_name',
                'users.last_name',
                'users.phone_number'
            ])
            ->where('roles.entity_key', $request->user()->role->entity_key)
            ->where('roles.entity_id', $request->user()->role->entity_id)
            ->where('roles.role', 'admin')
            ->where('users.id', '!=', $request->user()->id)
            ->whereNull('roles.deleted_at')
            ->get();

        return response($admins);

    }

    public function store(StoreContractorAdminRequest $request)
    {
        //If user exists, fetch it
        $user = User::where('email', $request->get('email'))->first();

        $exists = false;

        //If user doesn't exist, create
        if (!$user) {

            $exists = true;

            $request->merge(["password" => bcrypt($request->get('password'))]);

            $user = User::create($request->all());
        } //If user exists with admin role for this contracting organization, 409, user already exists
        else if ($user->roles()->where('entity_id', $request->user()->role->entity_id)->where('entity_key', $request->user()->role->entity_key)->where('role', 'admin')->first()) {
            return response(["message" => "User already exists"], 409);
        }

        //create user
        $role = Role::create([
            "user_id" => $user->id,
            "entity_key" => "contractor",
            "entity_id" => $request->user()->role->entity_id,
            "role" => "admin"
        ]);

        if ($exists){
            return response([
                "user" => $user,
                "message" => __('auth.role_creation')
            ], 202);
        }


        return response([
            "user" => $user
        ]);
    }

    //DEPRECATED AND DISABLed FOR SECURitY
    public function update(UpdateContractorAdminRequest $request, User $user)
    {
        //update password if in request
        if ($request->has('password')) {
            $request->merge(["password" => bcrypt($request->get('password'))]);
        }

        $user->update($request->all());

        return response($user);
    }

    /**
     * Destroy user role
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function destroy(Request $request, User $user)
    {

         Role::where('entity_id', $request->user()->role->entity_id)
            ->where('entity_key', $request->user()->role->entity_key)
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->delete();

        return response(["message" => "ok"], 200);

    }

}
