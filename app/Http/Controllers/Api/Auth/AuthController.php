<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;

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
                        'name' => 'required',
                        'email' => 'required|email',
                        'password' => 'required',  
                        'c_password' => 'required|same:password', 
                    ]);
          
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);                        
        } 

        $input = $request->all();  

        $input['password'] = bcrypt($input['password']);

        $user = User::create($input); 

        $success['token'] =  $user->createToken('accessToken')->accessToken;

        return response()->json(['success'=>$success], $this->successStatus); 
    }

    /**
     * 
     * Login a user
     * 
     */
    public function login() { 
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) { 
           
            $user = Auth::user(); 

            $success['token'] =  $user->createToken('accessToken')->accessToken; 
            
            return response()->json(['success' => $success], $this->successStatus); 
        } else { 
            return response()->json(['error'=>'Unauthorised'], $this->errorStatus); 
        } 
    }

    /**
     * 
     * Get a user information
     * 
     */
    public function getUser() {
        
        $user = Auth::user();

        return response()->json(['success' => $user], $this->successStatus); 
    }

}
