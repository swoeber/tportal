<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mail;
use \Laravel\Passport\Client;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function resetPassword(Request $request)
    {
        $valid = validator(
            $request->only(
                'email', 'new_password', 'confirm_password', 'current_password'
            ),
            [
                'email' => 'required|string|email|exists:users,email',
                'new_password' => 'required|string|min:6',
                'confirm_password' => 'required|string|min:6|same:new_password',
                'current_password' => 'required|string|min:6',
            ]
        );

        if ($valid->fails()) {
            return response()->json(
                [
                    'code' => 400,
                    'message' => 'Invalid please check to make sure the information provided is correct.',
                ]
            );
        }

        $data = request()->only(
            'email', 'new_password', 'confirm_password', 'current_password'
        );
        $currentPass = \Auth::user()->password;

        if (Hash::check($request->input('current_password'), $currentPass)) {
            $obj_user = User::where('email', $data['email'])->first();
            $obj_user->password = Hash::make($data['new_password']);
            $obj_user->save();

            return response()->json(
                ['code' => 200, 'message' => 'Your password has been updated.']
            );
        } else {
            return response()->json(
                ['code' => 400, 'message' => 'Please check your current password.']
            );
        }
    }

    public function resetForgotPassword(Request $request)
    {
        $valid = validator(
            $request->only('token', 'email', 'password', 'confirm_password'), [
                'email' => 'required|string|email|exists:users,email',
                'token' => 'required|string',
                'password' => 'required|string|min:6',
                'confirm_password' => 'required|string|min:6|same:password',
            ]
        );

        if ($valid->fails()) {
            return response()->json(['code' => 400, 'message' => 'Invalid please check to make sure the information provided is correct.']);
        }

        $data = request()->only('token', 'email', 'password', 'confirm_password');

        $token = User::getPasswordResetToken($data['email']);

        if (\Hash::check($data['token'], $token->token)) {
            User::removePasswordResetTokens($data['email']);

            $obj_user = User::where('email', $data['email'])->first();
            $obj_user->password = Hash::make($data['password']);
            $obj_user->save();

            return response()->json(['code' => 200, 'message' => 'Your password has been updated.']);
        } else {
            return response()->json(
                [
                    'code' => 400,
                    'message' => 'Invalid please check to make sure the information provided is correct.',
                ]
            );
        }
    }

    public function forgotPassword(Request $request)
    {
        $valid = validator(
            $request->only('email'),
            [
                'email' => 'required|string|email',
            ]
        );

        if ($valid->fails()) {
            return response()->json(['code' => 400, 'message' => 'Invalid User']);
        }

        $data = request()->only('email');

        if ($user = User::where('email', $data['email'])->first()) {
            $token = str_random(8);

            DB::table(config('auth.passwords.users.table'))->insert(
                [
                    'email' => $user->email,
                    'token' => Hash::make($token),
                ]
            );

            Mail::raw(
                'Please enter this code to reset your password in the Tavern Portal App : ' . $token,
                function ($message) use ($user) {
                    $message->subject('Password Reset!');
                    $message->from('no-reply@woeber.tech', 'Tavern Portal');
                    $message->to($user->email);
                }
            );

            return response()->json(
                ['code' => 200, 'message' => 'Token sent to your email!']
            );
        }

        return response()->json(['code' => 400, 'message' => 'Invalid User']);
    }

    public function login(Request $request)
    {
        $valid = validator(
            $request->only('email', 'password'), [
                'email' => 'required|string|email',
                'password' => 'required|string|min:6',
            ]
        );

        if ($valid->fails()) {
            return response()->json(
                ['code' => 400, 'payload' => $valid->errors()->all()]
            );
        }

        $data = request()->only('email', 'password');

        if (\Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            $client = Client::where('password_client', 1)->first();

            $request->request->add(
                [
                    'grant_type' => 'password',
                    'client_id' => $client->id,
                    'client_secret' => $client->secret,
                    'username' => $data['email'],
                    'password' => $data['password'],
                    'scope' => null,
                ]
            );

            // Fire off the internal request.
            $token = Request::create(
                'oauth/token',
                'POST'
            );

            return \Route::dispatch($token);
        } else {
            return response()->json(['code' => 400, 'message' => "Login Error"]);
        }
    }

    public function postUpdateUserName(Request $request) 
    {
        $valid = validator(
            $request->only('username'), [
                'username' => ['required', 'string', 'max:64', 'unique:users', 'regex:/[a-zA-Z0-9-]+/'],
            ]
        );

        if ($valid->fails()) {
            return response()->json(
                ['code' => 400, 'payload' => $valid->errors()->all()]
            );
        }
        
        $data = request()->only('username');

        $user = Auth::user();
        $user->username = $data['username'];
        $user->save();

        return response()->json(
            ['code' => 200, 'message' => 'Username Updated.']
        );
    }

    public function create(Request $request)
    {
        /**
         * Get a validator for an incoming registration request.
         */
        $valid = validator(
            $request->only('email', 'username', 'password', 'first_name', 'last_name'),
            [
                'username' => ['required', 'string', 'max:64', 'unique:users', 'regex:/[a-zA-Z0-9-]+/'],
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]
        );

        if ($valid->fails()) {
            // $jsonError=response()->json($valid->errors()->all(), 400);
            return response()->json(['code' => 400, 'payload' => $valid->errors()->all()]);
        }

        $data = request()->only('email', 'username', 'first_name', 'last_name', 'password');

        $user = User::create(
            [
                'username' => $data['username'],
                'name' => join(' ', [$data['first_name'], $data['last_name']]),
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]
        );

        // And created user until here.

        $client = Client::where('password_client', 1)->first();

        // Is this $request the same request? I mean Request $request? Then wouldn't it mess the other $request stuff? Also how did you pass it on the $request in $proxy? Wouldn't Request::create() just create a new thing?

        $request->request->add(
            [
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'username' => $data['email'],
                'password' => $data['password'],
                'scope' => null,
            ]
        );

        // Fire off the internal request.
        $token = Request::create(
            'oauth/token',
            'POST'
        );

        return \Route::dispatch($token);
    }
}
