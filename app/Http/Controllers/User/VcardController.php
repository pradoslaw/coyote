<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;

class VcardController extends Controller
{
    /**
     * @param \Coyote\User
     * @param UserRepository $repository
     * @return mixed
     */
    public function index($user, UserRepository $repository)
    {
        return view('components.vcard')->with('user', $user)->with([
            'rank'                  => $repository->rank($user->id),
            'total_users'           => $repository->countUsersWithReputation()
        ]);
    }
}
