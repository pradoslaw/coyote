<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Reputation;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Moje konto', route('user.home'));

        return parent::view('user.home', [
            'rank'                  => Reputation::getUserRank(auth()->user()->id),
            'total_users'           => Reputation::getTotalUsers()
        ]);
    }
}
