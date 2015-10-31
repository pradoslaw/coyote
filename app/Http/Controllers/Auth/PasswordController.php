<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class PasswordController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function getReset()
    {
        $this->breadcrumb->push('Odzyskiwanie hasła', '/password/reset');

        return parent::view('auth.reset');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        $this->breadcrumb->push('Odzyskiwanie hasła', '/password');


        return parent::view('auth.password');
    }
}
