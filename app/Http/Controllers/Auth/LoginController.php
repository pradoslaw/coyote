<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Logowanie', '/login');

        return parent::view('auth.login');
    }

    public function signin()
    {
    }
}
