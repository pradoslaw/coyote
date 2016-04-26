<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;

class VcardController extends Controller
{
    /**
     * @param $id
     * @param UserRepository $user
     * @param SessionRepository $session
     * @return mixed
     */
    public function index($id, UserRepository $user, SessionRepository $session)
    {
        $data = $user->find($id);
        if (!$data) {
            return response();
        }

        return view('components.vcard')->with('user', $data)->with([
            'session_at'            => $session->userLastActivity($data->id),
            'rank'                  => $user->rank($data->id),
            'total_users'           => $user->countUsersWithReputation()
        ]);
    }
}
