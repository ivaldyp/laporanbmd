<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
    // protected $redirectTo = RouteServiceProvider::HOME;
    protected $redirectTo = "/home";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'name';
    }

    protected function attemptLogin(Request $request)
    {
        if (is_numeric($request->name) && strlen($request->name) == 6) {
            $user = \App\User::where([
                'nrk_emp' => $request->name,
                'passmd5' => md5($request->password)
            ])->first();
        } elseif (is_numeric($request->name) && strlen($request->name) == 18) {
            $user = \App\User::where([
                'nip_emp' => $request->name,
                'passmd5' => md5($request->password)
            ])->first();

            if ($user) {
                $this->guard('login')->login($user);

                return true;
            }
            return false;
        } elseif (substr($request->name, 1, 1) == '.') {
            $user = \App\User::where([
                'id_emp' => $request->name,
                'passmd5' => md5($request->password)
            ])->first();
        } else {
            $user = \App\User::where([
                'usname' => $request->name,
                'passmd5' => md5($request->password)
            ])->first();

            if ($user) {
                $this->guard('login')->login($user);

                return true;
            }
            return false;
        }

        if ($user) {
            $this->guard('web')->login($user);

            return true;
        }

        return false;
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'passmd5');
    }
}
