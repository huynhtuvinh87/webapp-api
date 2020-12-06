<?php

namespace App\Http\Controllers\Api;

use Auth;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ControllerTrait;

class BaseAuthController extends Controller
{

    use SendsPasswordResetEmails, ResetsPasswords {
        SendsPasswordResetEmails::broker insteadof ResetsPasswords;
        ResetsPasswords::credentials insteadof SendsPasswordResetEmails;
    }
    use ControllerTrait;

    /**
     * Login the user using the API.
     * @param Request $request
     * @return mixed
     */
    protected function login(Request $request)
    {
        $this->logRequest($request, __METHOD__);
        $credentials = $request->only('email', 'password');

        $this->rehash(User::where('email', $request->get('email'))->first(), $request->password);

        if (!Auth::attempt($credentials)) {
            return false;
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return $tokenResult->accessToken;
    }

    /**
     * If md5 password provided matches user's password in DB, rehash with bcrypt and save
     * @param $user
     * @param $password
     */
    private function rehash($user, $password)
    {

        if ($user && $user && md5($password) === $user->password) {
            $user->password = bcrypt($password);
            $user->save();
        }

    }

    /**
     * OVERRIDING password reset routes
     */
    protected function sendResetLinkResponse($response)
    {

        return response()->json("Password reset email sent", 200);

    }

    protected function sendResetLinkFailedResponse($request, $response)
    {
        return response()->json("Email could not be sent to this email address", 400);
    }

    protected function resetPassword($user, $password)
    {
        $user->password = Bcrypt($password);
        $user->save();

        event(new PasswordReset($user));
    }

    protected function sendResetPassword($response)
    {
        return response()->json([
            "message" => "Password reset successful"
        ]);
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return response()->json([
            "message" => "Token Invalid"
        ], 400);
    }

    protected function sendResetResponse($response)
    {
        return response()->json([
            "message" => "Successfully changed password"
        ], 200);
    }
}
