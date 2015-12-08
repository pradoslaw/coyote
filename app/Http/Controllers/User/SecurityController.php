<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\User;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    /**
     * @return $this
     */
    public function index()
    {
        $this->breadcrumb->push('Moje konto', route('user.home'));
        $this->breadcrumb->push('Bezpieczeństwo', route('user.security'));



        return parent::view('user.security');
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function save(Request $request)
    {
        //
    }
}
