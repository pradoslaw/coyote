<?php

namespace Coyote\Http\Controllers\Api;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LoginController extends Controller
{
    use AuthenticatesUsers, ValidatesRequests;

    /**
     * @param Request $request
     * @return mixed
     */
    public function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        return $this->guard()->user()->createToken('4programmers.net')->accessToken;
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'name';
    }
}
