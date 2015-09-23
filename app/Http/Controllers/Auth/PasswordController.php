<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Http\Controllers\Controller;

class PasswordController extends Controller
{

    /**
     * @return Response
     */
    public function getReset()
    {
        $this->breadcrumb->push('Odzyskiwanie hasła', '/password/reset');

        return parent::view('auth/reset');
    }

    public function getIndex()
    {
        $this->breadcrumb->push('Odzyskiwanie hasła', '/password');

        return parent::view('auth/password');
    }

}
