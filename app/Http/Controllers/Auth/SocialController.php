<?php

namespace App\Http\Controllers\Auth;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SocialAccountService;
use Socialite;
use App\User;
use App\Traits\PassportToken;

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

    use PassportToken;

        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest')->except('logout');
    }

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

        $validator = Validator::make($request->all(), [
            "fb_token" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => self::ERROR_INVALID,
                "message" => $validator->messages()]);
        }

        $user = Socialite::driver($provider)->userFromToken($request->get('fb_token'));
        
        $user = $service->createOrGetUser($user, $provider);

        // $user = User::find(1);

        return $this->getBearerTokenByUser($user, 2, true);

        // auth()->login($user);

        // return response()->json([
        //         "error" => self::ERROR_INVALID,
        //         "message" => "success",
        //         "payload" => []
        //     ]);

    }

    public function handleProviderCallback(SocialAccountService $service, $provider)
    {
        $user = $service->createOrGetUser(Socialite::driver($provider)->user(), $provider);
        
        auth()->login($user);

        return redirect()->to($this->redirectTo);
    }


}
