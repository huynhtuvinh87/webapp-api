<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\RoleChangeRequest;
use App\Jobs\GetContractorSubscription;
use App\Models\Module;
use App\Models\ModuleVisibility;
use App\Models\Role;
use App\Models\File;
use App\Traits\CacheTrait;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Contractor;
use App\Http\Requests\ForgotPasswordRequest;
use Hash;
use Exception;
use App\Traits\FileTrait;
use App\Traits\ControllerTrait;
use App\Traits\ErrorHandlingTrait;

class AuthController extends BaseAuthController
{

    use FileTrait;
    use ControllerTrait;
    use ErrorHandlingTrait;
    use CacheTrait;

    /**
     * Login the users.
     *
     * @param Request $request email,password
     * @return \Illuminate\Contracts\Routing\ResponseFactory|mixed|\Symfony\Component\HttpFoundation\Response AccessToken, Role
     */
    public function login(Request $request)
    {
        $token = parent::login($request);

        if (!$token) {
            // Wrong credentials
            return response([
                'message' => __('auth.failed'),
            ], 401);
        }

        // If Authenticated
        $user = User::find(Auth::user()->id);
        $user->checkCurrentRole();
        $user->updateLastLogin();

        if (is_null($user->current_role_id)) {
            try {
                // TODO: Also take into consideration any roles that have been deleted
                $user->current_role_id = $user->roles()->orderBy('id', 'desc')->first()->id;
                $user->save();
            }
            catch(\Exception $e){
                Log::error($e->getMessage());
                Log::error('Role not found for user: '.$user->email);
                return response([
                    'message' => __('auth.failed'),
                ], 401);
            }
        }

        return response([
            'status' => 'success',
            'role' => $user->role,
            'access_token' => $token
        ])->header('Authorization', $token);
    }

    /**
     * Get the authenticated API user data.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Responsee
     */
    public function user(Request $request)
    {
        $user = $request->user();

        if ($user->current_role_id === null) {
            try {
                $user->current_role_id = $user->roles->first()->id;
                $user->save();
            }
            catch(\Exception $e){
                Log::error('Role not found for user: '.$user->email);
                return response([
                    'message' => __('auth.failed'),
                ], 401);
            }
		}

		if(!$user->role){
		    return response(["message" => "Role not found or not active"], 404);
        }

		$hasActiveSubscription = false;

		try{
			$modelUser = User::find(Auth::user()->id);
            $company = $user->role->company;
            if(get_class($company) == Contractor::class){
                $company->updateStripeInfo();
                $hasActiveSubscription = $company->subscribed('default');
            }
		} catch (Exception $e){
			Log::error($e->getMessage());
        }

        //Modules
        $modules = [];
        $enabled_modules = ($user->role->company->moduleVisibility) ?? null;
		foreach ($enabled_modules as $result){
            $found_mod = Module::find($result->module_id);
		    $modules[] = [
		        'id' => $result->module_id,
                'name' => $found_mod->name,
            ];
        }

        return response([
            'status' => 'success',
            'data' => $user,
            'role' => $user->role,
            'modules' => $modules,
            'avatar' => $user->avatarFile,
			'subscribed' => $hasActiveSubscription,
            'is_active' => (bool)$company->is_active ?? true,
        ]);
    }

    /**
     * Change user's role
     * @param RoleChangeRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function roleChange(RoleChangeRequest $request, Role $role)
    {

        $request->user()->current_role_id = $role->id;
        $request->user()->save();
        return response()->json($request->user());
    }

    /**
     * Log out the user.
     * @param $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function logout(Request $request)
    {
        $this->logRequest($request, __METHOD__);
        //JWTAuth::invalidate();
        $request->user()->token()->revoke();
        return response([
            'message' => 'Logged out Successfully.',
        ], 200);
    }

    /**
     * Check if the's an authenticated user or not.
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function check()
    {
        return response([
            'status' => Auth::user() ? true : false
        ], 200);
    }


    /**
     * @param ForgotPasswordRequest $request email
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @action send email
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $this->logRequest($request, __METHOD__);

        $user = User::where('email', $request->get('email'))->first();

        $userExists = isset($user);

        if (!$userExists) {
            return response([
                'message' => 'Oops, it looks like you\'re trying to reset your password, but you have not registered with Contractor Compliance yet.  For assistance in registering, please click "NEW CONTRACTOR SIGN UP" or contact us directly at support@contractorcompliance.io',
            ],
                402);
        }

        if ($user->roles()->count() === 1 && $user->roles()->first()->entity_key === 'contractor' && !$user->roles()->first()->company->stripe_id) {
            return response([
                'message' => 'Oops, it looks like you\'re trying to reset your password, but you have not registered with Contractor Compliance yet.  For assistance in registering, please click "NEW CONTRACTOR SIGN UP" or contact us directly at support@contractorcompliance.io',
                ],
                402);
        }

        return $this->sendResetLinkEmail($request);
    }

    /**
     * @param Request $request email,password,password_confirmation
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function doReset(Request $request)
    {
        $reset = $this->reset($request);
        return $reset;
    }

    //TODO Validation in Request class

    /**
     * @param Request $request password,new_password,new_password_confirmation
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setPassword(Request $request)
    {
        try{
            $this->logRequest($request, __METHOD__);
            $user = $request->user();

            if($user->has_password){
                $validationSchema['password'] = "required";
                if(!$request->has('password')){
                    throw new Exception("Original password was not defined");
                }
            }
            $validationSchema = [
                "new_password" => "required|confirmed",
            ];
            $this->validate($request, [
                $validationSchema
            ]);

            if($user->has_password){
                if (!Hash::check($request->get('password'), $user->password)) {
                    throw new Exception("Unable to process your request");
                }
            }

            $user->password = bcrypt($request->get('new_password'));
            $user->save();

            return response()->json("Password Reset", 200);
        } catch (Exception $e){
            return $this->errorResponse($e, 400);
        }

    }

    //TODO Validation in Request class

    /**
     *
     * @param Request $request {User}
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateProfile(Request $request)
    {

        $user = $request->user();

        if ($request->has('email') && $request->get('email') === $user->email) {
            unset($request['email']);
        }

        $this->validate($request, [
            "email" => "email|max:80|unique:users",
            "avatar" => "image",
            "first_name" => "max:30",
            "last_name" => "max:30",
            "phone_number" => "max:14"
        ]);

        if ($request->hasFile('avatar')) {

            $newFile = $this->createFileFromRequest($request, 'avatar');
            $user->update([
                'avatar_file_id' => $newFile->id
            ]);

        }

        $user->update($request->except('avatar', 'password'));

        $user->save();

        return response()->json($user, 200);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse {Array roles, Object role}
     */
    public function getRoles(Request $request)
    {
        return response()->json([
            "roles" => $request->user()->roles()->with(['company' => function($query){
                $query->select('id', 'name');
            }])->get(),
            "current_role" => $request->user()->role()->with(['company' => function($query){
                $query->select('id', 'name');
            }])->get(),
        ]);
    }

    /**
     * @param Request $request role_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setCurrentRole(Request $request)
    {

        $this->validate($request, [
            "role_id" => "required|integer|exists:roles,id",
        ]);

        if (Role::find($request->find('role_id'))->user_id !== $request->user()->id) {
            return response()->json("User does not own role", 403);
        }

        $request->user()->current_role_id = $request->get('role_id');

        return response()->json('OK', 200);
    }

    /**
     * Get a list of users that are contactable for the current user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    //DEPRECATED
    public function contactable(Request $request)
    {
        return response()->json([
            "contactable" => $request->user()->employeeCommunication()
        ]);

    }
}
