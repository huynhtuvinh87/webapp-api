<?php

namespace App\Http\Controllers\Admin;

use App\Models\HiringOrganization;
use App\Http\Requests\Admin\CreateOrganization; //TODO
use App\Models\Role;
use App\Models\User;
use App\Traits\CacheTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use DB;
use Log;
use Exception;

class HiringOrganizationController extends Controller
{
    use CacheTrait;

    public function index(Request $request){

        if ($request->wantsJson()){
            return response()->json(["orgs" => HiringOrganization::where(function($query) use ($request){

                //Only execute if search is specified
                if (!$request->has('search')){
                    return;
                }

                $query->where('name', 'LIKE', "%".$request->get('search')."%");

            })->paginate(20)]);
        }

        return view('admin.hiringorg.index');

    }

    public function show(Request $request, $id){


        //Reference UserController for explanation
        if (\Illuminate\Support\Facades\Request::wantsjson()){

            $users = DB::table('users')
                ->leftJoin('roles', 'users.id', '=', 'roles.user_id')
                ->leftJoin('hiring_organizations', 'hiring_organizations.id', '=', 'roles.entity_key')
                ->where(function($query) use ($request){

                    if (!$request->has('search')){
                        return;
                    }

                    $query->where('users.email', 'LIKE', '%'.$request->input('search').'%')
                        ->orWhere('hiring_organizations.name', 'LIKE', '%'.$request->input('search').'%');
                })
                ->select(
                    "users.id as user_id",
                    "users.email",
                    "roles.role",
                    "roles.entity_key",
                    "hiring_organizations.name as hiring_org"
                )
                ->where('roles.entity_key', 'hiring_organization')
                ->where('roles.entity_id', $id)
                ->orderBy('users.id')
                ->paginate($request->input('rowsPerPage', 10));

            return response()->json(["users" => $users]);

        }

        $org = HiringOrganization::find($id);

        return view('admin.hiringorg.show', ["org" => $org]);
    }

    /**
     * Create a hiring organization with an associated user/role administrator
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request){

        $this->validate($request, [
            "name" => "required|unique:hiring_organizations|max:45",
            "email" => "required|max:80",
            "password" => "required|max:255",
            "first_name" => "max:30",
            "last_name" => "max:30"
        ]);


            try {

                DB::beginTransaction();

                $org = HiringOrganization::create([
                    "name" => $request->get('name')
                ]);

                $user = User::where('email', $request->get('email'))->first();

                if (!$user){
                    $user = User::create([
                        "first_name" => $request->get('first_name'),
                        "last_name" => $request->get('last_name'),
                        "email" => $request->get('email'),
                        "password" => bcrypt($request->get('password'))
                    ]);
                }

                $role = Role::create([
                    "entity_key" => "hiring_organization",
                    "entity_id" => $org->id,
                    "role" => "owner",
                    "user_id" => $user->id
                ]);

                DB::commit();

                return response()->json([
                    "user" => $user,
                    "role" => $role,
                    "hiring_organization" => $org
                ]);

            }
            catch(\Exception $e){

                DB::rollback();

                return response()->json([
                    "errors" => $e
                ], 500);

            }
    }

    /**
     * Enable/Disables HO Status
     * @param HiringOrganization $hiring_organization
     * @return null
     */
    public function toggleIsActive(HiringOrganization $hiring_organization){

        $hiring_organization->is_active = !$hiring_organization->is_active;
        $hiring_organization->save();

        Cache::tags([$this->getHiringOrgCacheTag($hiring_organization)])->flush();

        return response(['message' => "Hiring Organization enabled / disabled"], 200);
    }


    /**
     * Takes in an ID and clears the elements cache
     *
     * @param $id
     * @return void
     */
    public function clearCache($id)
    {
        $obj = HiringOrganization::find($id);
        $className = $this->getClassName($obj);
        $success = false;
        $message = null;

        try {
            Cache::tags($this->buildTagFromObject($obj))->flush();
            $success = true;
            $message = $obj->name . " cache was cleared successfully";
        } catch (Exception $e) {
            Log::warn("Failed to clear cache for '$id'.", [
                $className => $obj,
                'Exception' => $e
            ]);
            $success = false;
            $message = "Failed to clear cache";
        }
            // return redirect('/admin/users/' . $id);
            return response()->view('admin.contractor.index', ['contractor' => $obj, "success" => $success, "message" => $message]);
    }
}
