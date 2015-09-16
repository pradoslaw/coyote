<?php

namespace Coyote\Http\Controllers\Auth;
use Breadcrumb\Breadcrumb;
use Coyote\Http\Controllers\Controller;

class RegisterController extends Controller {

    /**
     * @return Response
     */
    public function getIndex()
    {
        $this->breadcrumb->push('Rejestracja', '/register');

        return view('auth/register', ['breadcrumb' => $this->breadcrumb->render()]);
    }

    public function postIndex()
    {

    }

}
