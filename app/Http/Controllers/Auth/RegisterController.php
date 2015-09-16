<?php

namespace Coyote\Http\Controllers\Auth;
use Coyote\Http\Controllers\Controller;

class RegisterController extends Controller {

    /**
     * @return Response
     */
    public function getIndex()
    {
        return view('auth/register');
    }

    public function postIndex()
    {

    }

}
