<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\User;
use App\PasswordReset;

class PasswordResetController extends Controller
{
    //
    public $successStatus = 200;
    public $errorStatus = 401;
    public $notFoundStatus = 404;

    /**
     * Create token password reset
     *
     * @param  [string] email
     * @return [string] message
     */
    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) return response()->json([ 'success' => true, 'error' => "We can't find a user with that e-mail address." ], $this->successStatus);
        
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => str_random(60) //token generate or link generation
             ]
        );

        if ($user && $passwordReset)
            $user->notify(
                new PasswordResetRequest($passwordReset->token)
            );
        return response()->json([ 'success' => true, 'data' => 'We have e-mailed your password reset link!'], $this->successStatus);
    }

    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return [string] message
     * @return [json] passwordReset object
     */
    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset) return response()->json(['success' => false, 'error' => 'This password reset token is invalid.'], $this->notFoundStatus);

        //token expired time is 30 Minutes
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(30)->isPast()) {
            $passwordReset->delete();
            return response()->json([ 'success' => false, 'error' => 'This password reset token is invalid.'], $this->notFoundStatus);
        }

        return response()->json(['success' => true, 'data' => $passwordReset], $this->successStatus);
    }

    /**
     * Reset password
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] token
     * @return [string] message
     * @return [json] user object
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset) return response()->json([ 'success' => false, 'error' => 'This password reset token is invalid.'], $this->notFoundStatus);
        
        $user = User::where('email', $passwordReset->email)->first();
        if (!$user) return response()->json([ 'success' => false, 'error' => "We can't find a user with that e-mail address."], $this->notFoundStatus);
        
        $user->password = bcrypt($request->password);

        $user->save();

        $passwordReset->delete();

        $user->notify(new PasswordResetSuccess($passwordReset));

        return response()->json(['success' => true, 'data' => $user], $this->successStatus);
    }
}
