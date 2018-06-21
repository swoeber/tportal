<?php

namespace App\Http\Controllers\Api;

use DB, Mail, Hash;
use \Laravel\Passport\Client;
use Illuminate\Http\Request;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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

    function forgotPassword(Request $request) {
        $valid = validator($request->only('email', 'password'), [
            'email' => 'required|string|email',
        ]);

        if ($valid->fails()) {
            return response()->json([ 'code' => 400, 'message' => 'Invalid User']);
        }

        $data = request()->only('email');

        if( $user = User::where('email', $data['email'] )->first() )
        {
            $token = str_random(8);

            DB::table(config('auth.passwords.users.table'))->insert([
                'email' => $user->email, 
                'token' => Hash::make($token)
            ]);

            Mail::raw('Please enter this code to reset your password in the Tavern Portal App : ' . $token, function($message) use ($user)
            {
                $message->subject('Password Reset!');
                $message->from('no-reply@woeber.tech', 'Tavern Portal');
                $message->to($user->email);
            });

            return response()->json([ 'code' => 200, 'message' => 'Token sent to your email!']);
        }

        return response()->json([ 'code' => 400, 'message' => 'Invalid User']);
    }

    function login(Request $request)
    {
        $valid = validator($request->only('email', 'password'), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);

        if ($valid->fails()) {
            return response()->json([ 'code' => 400, 'payload' =>  $valid->errors()->all()]);
        }

        $data = request()->only('email','password');

        if (\Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            $client = Client::where('password_client', 1)->first();

            $request->request->add([
                'grant_type'    => 'password',
                'client_id'     => $client->id,
                'client_secret' => $client->secret,
                'username'      => $data['email'],
                'password'      => $data['password'],
                'scope'         => null,
            ]);

            // Fire off the internal request. 
            $token = Request::create(
                'oauth/token',
                'POST'
            );

            return \Route::dispatch($token);
        } else {
            return response()->json([ 'code' => 400, 'message' => "Login Error" ]);
        }
    }
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    function create(Request $request)
    {
        /**
         * Get a validator for an incoming registration request.
         *
         * @param  array  $request
         * @return \Illuminate\Contracts\Validation\Validator
         */
        $valid = validator($request->only('email', 'username', 'password', 'first_name', 'last_name'), [
            'username' => ['required','string','max:64','unique:users','regex:/[a-zA-Z0-9-]+/'],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        if ($valid->fails()) {
            // $jsonError=response()->json($valid->errors()->all(), 400);
            return response()->json([ 'code' => 400, 'payload' =>  $valid->errors()->all()]);
        }

        $data = request()->only('email','username', 'first_name','last_name','password');

        $user = User::create([
            'username' => $data['username'],
            'name' => join(' ', [$data['first_name'], $data['last_name']]) ,
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        // And created user until here.

        $client = Client::where('password_client', 1)->first();

        // Is this $request the same request? I mean Request $request? Then wouldn't it mess the other $request stuff? Also how did you pass it on the $request in $proxy? Wouldn't Request::create() just create a new thing?

        $request->request->add([
            'grant_type'    => 'password',
            'client_id'     => $client->id,
            'client_secret' => $client->secret,
            'username'      => $data['email'],
            'password'      => $data['password'],
            'scope'         => null,
        ]);

        // Fire off the internal request. 
        $token = Request::create(
            'oauth/token',
            'POST'
        );

        return \Route::dispatch($token);
    }
}
