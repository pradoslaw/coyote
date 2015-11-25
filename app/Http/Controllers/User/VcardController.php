<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\SessionRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Session;

class VcardController extends Controller
{
    /**
     * @param $id
     * @param UserRepositoryInterface $user
     * @param SessionRepositoryInterface $session
     * @return mixed
     */
    public function index($id, UserRepositoryInterface $user, SessionRepositoryInterface $session)
    {
        $data = $user->find($id);
        if (!$data) {
            exit;
        }

        return view('components.vcard')->with('user', $data)->with([
            'session_at'            => $session->userLastActivity(auth()->user()->id),
            'rank'                  => $user->rank(auth()->user()->id),
            'total_users'           => $user->countUsersWithReputation()
        ]);
    }
}
