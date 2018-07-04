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

Route::post('/register', 'Api\UserController@create');
Route::post('/login', 'Api\UserController@login');
Route::post('/forgotpassword', 'Api\UserController@forgotPassword');
Route::post('/resetforgotpassword', 'Api\UserController@resetForgotPassword');

Route::middleware('auth:api')->group(
    function () {
        /*
            [ User Items ]
        */
        Route::post('/resetpassword', 'Api\UserController@resetPassword');
        Route::post('/update-user-name', 'Api\UserController@postUpdateUserName');

        Route::get(
            '/user',
            function (Request $request) {
                return $request->user();
            }
        );
    }
);
