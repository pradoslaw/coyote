<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Moje konto', route('user.home'));

        return parent::view('user.home');
    }
}
