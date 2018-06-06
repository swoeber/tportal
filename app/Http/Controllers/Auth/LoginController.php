<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Services\SocialAccountService;
use Socialite;

class LoginController extends Controller
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

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectToProvider()
    {
        $data = explode("/", \Route::getFacadeRoot()->current()->uri());

        $provider = $data[1];

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(SocialAccountService $service)
    {
        $data = explode("/", \Route::getFacadeRoot()->current()->uri());
        $provider = $data[1];

        $user = $service->createOrGetUser(Socialite::driver($provider)->user(), $provider);
        auth()->login($user);

        return redirect()->to($this->redirectTo);
    }

    public function handleLogout()
    {
        auth()->logout();

        session()->flash('message', 'Some goodbye message');

        return redirect($this->redirectTo);
    }
    
}
