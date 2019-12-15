<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use Socialite;
use App\Services\SocialGoogleAccountService;
use App\Services\SocialFacebookAccountService;

class AuthController extends Controller
{
    //
    public $successStatus = 200;
    public $errorStatus = 401;

    /**
     * 
     * Register a User 
     * 
     */
    public function register(Request $request) {   
        $validator = Validator::make($request->all(), 
                     [ 
                        'name' => 'required|unique:users',
                        'email' => 'required|email|unique:users',
                        'password' => 'required',  
                        'c_password' => 'required|same:password', 
                    ]);
          
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error'=>$validator->errors()], $this->errorStatus);                        
        } 

        try {
            $input = $request->all();  

            $input['password'] = bcrypt($input['password']);
    
            $user = User::create($input); 
    
            $token =  $user->createToken('accessToken')->accessToken;
    
            return response()->json(['success'=> true, 'data' => $user, 'token' => $token ], $this->successStatus); 
        }
        catch (\Illuminate\Database\QueryException $exception) {
            return response()->json(['success'=> false, 'error' => $exception ], $this->errorStatus); 
        }
    }

    /**
     * 
     * Login a user
     * 
     */
    public function login() { 
            if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) { 
                $user = Auth::user(); 

                $token =  $user->createToken('accessToken')->accessToken; 
            
                return response()->json(['success' => true, 'data' => $user, 'token' => $token], $this->successStatus); 
            } else { 
                return response()->json(['success' => false, 'error'=>'User Not Found'], $this->errorStatus); 
            } 
    }

    /**
     * 
     * Get a user information
     * 
     */
    public function getUser() {

        $user = Auth::user();

        return response()->json(['success' => true, 'data' => $user], $this->successStatus); 
    }

    /**
     * 
     * Login with facebook
     * 
     */
    public function facebookCallback(SocialFacebookAccountService $service) {
        $user = $service->createOrGetUser(Socialite::driver('facebook')->user());
        
        auth()->login($user);

        $token =  $user->createToken('accessToken')->accessToken; 

        return response()->json(['success' => true, 'data' => $user, 'token' => $token], $this->successStatus); 
    }

    /**
     * 
     * Login with google
     * 
     */
    public function googleCallback(SocialGoogleAccountService $service) {
        $user = $service->createOrGetUser(Socialite::driver('google')->user());
        
        auth()->login($user);

        $token =  $user->createToken('accessToken')->accessToken; 

        return response()->json(['success' => true, 'data' => $user, 'token' => $token], $this->successStatus); 
    }
    

    /**
     * 
     * Retrieve user from the token
     */
    public function retrieveSocialUserFromToken($token, $services) {
        $user = Socialite::driver($services)->userFromToken($token);

        return response()->json(['success' => true, 'data' => $user, 'token' => $token], $this->successStatus); 
    }

}
