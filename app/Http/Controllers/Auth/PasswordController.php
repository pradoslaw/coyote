<?php

namespace Coyote\Http\Controllers\Auth;
use Coyote\Http\Controllers\Controller;

class PasswordController extends Controller {

    /**
     * @return Response
     */
    public function getReset()
    {
        return view('auth/reset');
    }

    public function getIndex()
    {
        return view('auth/password');
    }

}
