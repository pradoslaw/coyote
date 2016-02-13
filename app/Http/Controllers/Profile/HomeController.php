<?php

namespace Coyote\Http\Controllers\Profile;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as Reputation;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as Session;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;

class HomeController extends Controller
{
    private $user;
    private $session;

    public function __construct(User $user, Session $session, Reputation $reputation)
    {
        parent::__construct();

        $this->user = $user;
        $this->session = $session;
        $this->reputation = $reputation;
    }

    /**
     * @param \Coyote\User $user
     * @return \Illuminate\View\View
     */
    public function index($user)
    {
        $this->breadcrumb->push('Profil: ' . $user->name, route('profile', ['user' => 1]));

        return parent::view('profile.home')->with([
            'user'          => $user,
            'rank'          => $this->user->rank($user->id),
            'total_users'   => $this->user->countUsersWithReputation(),
            'reputation'    => $this->reputation->takeForUser($user->id)
        ]);
    }
}
