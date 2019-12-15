<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function(){

    Route::post('login', 'Api\Auth\AuthController@login');

    Route::post('register', 'Api\Auth\AuthController@register');

    Route::group(['middleware' => 'auth:api'], function() {

        Route::post('getUser', 'Api\Auth\AuthController@getUser');

        Route::post('/social/getUser/{services}/{token}', 'Api\Auth\AuthController@retrieveSocialUserFromToken');
    
    });

    /** Callback aith Social - Facebook and Google */
    Route::get ('/callback/facebook', 'Api\Auth\AuthController@facebookCallback');

    Route::get ('/callback/google', 'Api\Auth\AuthController@googleCallback');

    /** Change Password and Password Reset */
    Route::prefix('password')->group(function(){ 
        Route::post('create', 'Api\Auth\PasswordResetController@create');

        Route::get('find/{token}', 'Api\Auth\PasswordResetController@find');

        Route::post('reset', 'Api\Auth\PasswordResetController@reset');
    });
});