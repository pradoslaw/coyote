<?php

namespace Coyote\Http\Controllers\Profile;

use Coyote\Http\Controllers\Controller;
use Coyote\User;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index(User $user)
    {
        $this->breadcrumb->push('Profil: ' . $user->name, route('profile', ['user' => 1]));

        return parent::view('profile.home');
    }
}
