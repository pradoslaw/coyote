<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\View\View;

class VcardController extends Controller
{
    public function index(\Coyote\User $user, UserRepositoryInterface $repository): View
    {
        return view('legacyComponents.vcard')
          ->with('user', $user)
          ->with([
            'rank'        => $repository->rank($user->id),
            'total_users' => $repository->countUsersWithReputation()
          ]);
    }
}
