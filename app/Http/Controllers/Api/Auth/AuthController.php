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
use App\MSG91;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

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

    /**
    * Sending the OTP.
    *
    * @return Response
    */
    public function sendOtp(Request $request){
        $userId = Auth::user()->id;

        $users = User::where('id', $userId)->first();

        if (isset($users['phonenumber']) && $users['phonenumber'] == '' ) {

            return response()->json(['success' => false, 'error' => 'Invalid Mobie Number'], $this->errorStatus);

        } else {

            $otp = rand(100000, 999999);

            $MSG91 = new MSG91();


            $msg91Response = $MSG91->sendSMS($otp,$users['mobile']);

            if($msg91Response['error'])
            {

                return response()->json(['success' => false, 'error' => $msg91Response['message']], $this->errorStatus);

            } 
            else
            {

                Session::put('OTP', $otp);

                return response()->json(['success' => true, 'message' => 'OTP is sent successfully.', 'data' => $otp], $this->successStatus);
            }
        }
    }

    /**
    * Function to verify OTP.
    *
    * @return Response
    */
    public function verifyOtp(Request $request){

        $enteredOtp = $request->input('otp');

        $userId = Auth::user()->id;

        $sessionOtp = $request->session()->get('OTP');

        if($sessionOtp === $enteredOtp)
        {

            $user = User::where('id', $userId)->update(['phonenumber_verified_at' => Carbon::now()]);

            Session::forget('OTP');

            return response()->json(['success' => true, 'message' => 'Your Number Is Verified Successfully.', 'data' => $user], $this->successStatus);

        }
        else
        {
            return response()->json(['success' => false, 'error' => 'OTP does not match'], $this->errorStatus);
        }
    }

}
