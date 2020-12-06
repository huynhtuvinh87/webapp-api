<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\CacheTrait;
use App\Traits\ControllerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Log;
use Exception;

class UserController extends Controller
{
    use CacheTrait;
    use ControllerTrait;
    /**
     * TODO: Split view and user list routes
     * @param Request
     * @return View|JsonResponse
     * @return User
     */
    public function index(Request $request)
    {

        /**
         * Complex query to get all users:
         * Contractor/HiringOrganizations have similar structure with less joins
         * where closure is for search queryability - consider extending DB class for reusability
         */
        if (\Illuminate\Support\Facades\Request::wantsjson()) {

            $users = DB::table('users')
                ->leftJoin('roles', 'users.id', '=', 'roles.user_id')
            //->leftJoin('hiring_organizations', 'hiring_organizations.id', '=', 'roles.entity_key')
            //->leftJoin('contractors', 'contractors.id', '=', 'roles.entity_key')
                ->leftJoin('hiring_organizations', function ($join) {
                    $join->on('hiring_organizations.id', '=', 'roles.entity_id');
                    $join->where('roles.entity_key', '=', 'hiring_organization');
                })
                ->leftJoin('contractors', function ($join) {
                    $join->on('contractors.id', '=', 'roles.entity_id');
                    $join->where('roles.entity_key', '=', 'contractor');
                })
                ->leftJoin('subscriptions', 'contractors.id', '=', 'subscriptions.contractor_id')
                ->where(function ($query) use ($request) {

                    //Only execute if search is specified
                    if (!$request->has('search')) {
                        return;
                    }

                    $query->where('users.email', 'LIKE', '%' . $request->input('search') . '%')
                        ->orWhere('hiring_organizations.name', 'LIKE', '%' . $request->input('search') . '%')
                        ->orWhere('contractors.name', 'LIKE', '%' . $request->input('search') . '%');
                });

            $paginatedUsers = $users
                ->select(
                    "users.id as user_id",
                    "users.email",
                    "users.email_verified_at",
                    "roles.role",
                    "roles.entity_key",
                    "hiring_organizations.name as hiring_org",
                    "contractors.name as contractor",
                    "subscriptions.ends_at as sub_expiring"
                )
                ->orderBy('users.id')
                ->paginate($request->input('rowsPerPage', 10));

            // NOTE: Trying to get a count of all the users is very slow.
            // $countUsers = $users
            //     ->select(DB::raw("COUNT(*) as count"))
            //     ->first();

            return response()->json([
                "users" => $paginatedUsers
            ]);
        }

        return response()->view('admin.users.users');

    }

    public function edit($id)
    {

        return response()->view('admin.users.user', ["user" => User::find($id)]);

    }

    public function update(Request $request, $id)
    {

        if (!$request->get('password')) {
            $request->request->remove('password');
        } else {
            $request->merge([
                'password' => bcrypt($request->get('password')),
            ]);
        }

        User::find($id)->update($request->all());

        return redirect('/admin/users/' . $id);

    }

    public function makeAdmin(Request $request, $id)
    {
        $this->logRequest($request, __METHOD__);

        $user = User::find($id);

        $user->global_admin = 1;

        $user->save();

        return response()->json("Ok");

    }

    public function revokeAdmin(Request $request, $id)
    {
        $this->logRequest($request, __METHOD__);

        $user = User::find($id);

        $user->global_admin = 0;

        $user->save();

        return response()->json("Ok");

    }

    public function assume($id)
    {

        $user = User::find($id);

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        $token->save();

        $baseUrl = config('client.web_ui');

        // Fixing baseURL
        // Need URL to be the following: localhost:8080/#/
        // NOTE: If WEB_APP_URL is not put into quotes, `/#/` is removed from end
        if (substr($baseUrl, -3) != "/#/") {
            switch (substr($baseUrl, -3)) {
                case '80/':
                case 'io/':
                    $baseUrl .= '#/';
                    break;
                case '080':
                case '.io':
                    $baseUrl .= '/#/';
                    break;
            }
        }

        $redirectRoute = $baseUrl . "login/?access_token=$tokenResult->accessToken";

        return redirect($redirectRoute);

    }

    /**
     * Takes a user ID.
     * Clears the cache for the user, and all of their roles.
     *
     * @param $id
     * @return void
     */
    public function clearCache($id)
    {
        $user = User::find($id);
        $success = false;
        $message = null;

        try {
            // Clearing cache for user
            Cache::tags($this->buildTagFromObject($user))->flush();

            // Clearing cache for user roles
            $roles = $user->roles;
            $roleTags = $roles->map(function($role){
                return $this->buildTagFromObject($role);
            });

            Cache::tags($roleTags)->flush();
            $message = $user->email . " cache was cleared successfully";
            $success = true;
        } catch (Exception $e) {
            Log::warn("Failed to clear cache for user '$id'.", [
                'User' => $user,
                'Exception' => $e
            ]);
            $message = $user->email . " cache failed to clear";
            $success = false;
        }
        // return redirect('/admin/users/' . $id);
        return response()->view('admin.users.users', ["user" => $user, "success" => $success, "message" => $message]);
    }

}
