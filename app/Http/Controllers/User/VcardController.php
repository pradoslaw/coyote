<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\User;
use Coyote\Session;
use Coyote\Reputation;

class VcardController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        $user = User::find($id);
        if (!$user) {
            exit;
        }

        return view('components.vcard')->with('user', $user)->with([
            'is_online'             => Session::isUserOnline($id),
            'rank'                  => Reputation::getUserRank(auth()->user()->id),
            'total_users'           => Reputation::getTotalUsers()
        ]);
    }
}
