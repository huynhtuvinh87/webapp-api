<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\Registration\NewEmployee;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Exception;
use Log;
use Illuminate\Auth\Events\Verified;

// class VerificationController extends Controller
// {
//     /*
//     |--------------------------------------------------------------------------
//     | Email Verification Controller
//     |--------------------------------------------------------------------------
//     |
//     | This controller is responsible for handling email verification for any
//     | user that recently registered with the application. Emails may also
//     | be re-sent if the user didn't receive the original email message.
//     |
//      */

//     use VerifiesEmails;

//     /**
//      * Where to redirect users after verification.
//      *
//      * @var string
//      */
//     protected $redirectTo = '/home';

//     /**
//      * Create a new controller instance.
//      *
//      * @return void
//      */
//     public function __construct()
//     {
//         // $this->middleware('auth');
//         // $this->middleware('signed')->only('verify');
//         // $this->middleware('throttle:6,1')->only('verify', 'resend');
//     }
// }

// namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    use VerifiesEmails;
/**
 * Show the email verification notice.
 *
 */
    public function show()
    {
        Log::debug(__METHOD__);
    }

    public function sendVerification(Request $request)
    {
        try {

            $userID = $request["id"];
            $user = User::findOrFail($userID);
            $role = Role::where('user_id', $userID)->first();

            if(!isset($role)){
                throw new Exception("Role for user was not found");
            }
            if($role->user_id != $user->id){
                throw new Exception("Role->user_id mismatch.");
            }

            $user->password = null;
            $user->email_verified_at = null;
            $user->current_role_id = $role->id;
            $user->save();

            $user->notify(new NewEmployee($user));

            return view('admin.contractor.show', [
                "contractor" => $role->company,
                'alert' => [
                    'type' => 'success',
                    'text' => "Email sent to $user->email"
                ]
            ]);
        } catch (Exception $e){
            Log::error($e->getMessage());
            return response([
                'message' => "Failed to send verification",
                "error" => $e->getMessage()
            ], 400);
        }
    }
/**
 * Mark the authenticated user"s email address as verified.
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\Response
 */
    public function verify(Request $request)
    {

        Log::debug(__METHOD__);
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        try {
            $userID = $request["id"];
            $user = User::findOrFail($userID);

            if (!is_null($user->password)) {
                return response(['message' => 'Your account has already been verified, please use the login page to access system.']);
            }

            $date = Carbon::now()->toDateTimeString();
            $user->email_verified_at = $date; // to enable the “email_verified_at field of that user be a current time stamp by mimicing the must verify email feature
            $user->save();

            // TODO: Redirect user to home page
            // NOTE: Needs to log the user in
            // File: webapp-api/app/Http/Controllers/Admin/UserController.php
            // 143:     public function assume($id)

            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $token->save();

            $baseUrl = config('client.web_ui');

            $redirectRoute = $baseUrl . "login/?access_token=$tokenResult->accessToken";


            return redirect($redirectRoute);
        } catch (Exception $e) {
            return response(['message' => $e->getMessage()]);
        }
    }
/**
 * Resend the email verification notification.
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\Response
 */
    public function resend(Request $request)
    {
        $this->sendVerification($request);
    }
}
