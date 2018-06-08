<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Services\SocialAccountService;
use Socialite;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    // use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function loginUser(SocialAccountService $service, Request $request, $provider) 
    {
        $rules = [
            "fb_token" => "required"
        ];

        $data = $request->validate($rules);

        $user = Socialite::driver($provider)->userFromToken($data['fb_token']);
        
        $user = $service->createOrGetUser($user, $provider);

        auth()->login($user);

        response()->json(auth()->user());

    }

    public function handleProviderCallback(SocialAccountService $service, $provider)
    {
        $user = $service->createOrGetUser(Socialite::driver($provider)->user(), $provider);
        
        auth()->login($user);

        return redirect()->to($this->redirectTo);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
