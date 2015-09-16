<?php

namespace Coyote\Http\Controllers\Auth;
use Coyote\Http\Controllers\Controller;

class LoginController extends Controller {

    /**
     * @return Response
     */
    public function getIndex()
    {
        return view('auth/login');
    }

    public function postIndex()
    {

    }

}
