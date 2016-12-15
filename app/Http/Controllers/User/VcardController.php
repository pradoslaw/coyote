<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;

class VcardController extends Controller
{
    /**
     * @param \Coyote\User
     * @param UserRepository $repository
     * @param SessionRepository $session
     * @return mixed
     */
    public function index($user, UserRepository $repository, SessionRepository $session)
    {
        return view('components.vcard')->with('user', $user)->with([
            'session_at'            => $session->updatedAt($user->id),
            'rank'                  => $repository->rank($user->id),
            'total_users'           => $repository->countUsersWithReputation()
        ]);
    }
}
