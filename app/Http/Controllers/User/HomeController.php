<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * @return Response
     */
    public function getIndex()
    {
        $this->breadcrumb->push('Moje konto', '/User');

        return parent::view('user/home');
    }

    public function postIndex()
    {
    }
}
