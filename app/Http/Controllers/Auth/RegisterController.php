<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Http\Controllers\Controller;

class RegisterController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Rejestracja', '/register');

        return parent::view('auth.register');
    }

    public function signup()
    {
    }
}
