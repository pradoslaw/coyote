<?php

namespace Coyote\Http\Controllers\Profile;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Controllers\User\UserMenuTrait;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as Reputation;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as Session;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\User\Skill;

class HomeController extends Controller
{
    use UserMenuTrait;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Reputation
     */
    private $reputation;

    /**
     * HomeController constructor.
     *
     * @param User $user
     * @param Session $session
     * @param Reputation $reputation
     */
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
        $this->breadcrumb->push($user->name, route('profile', ['user' => $user->id]));

        return $this->view('profile.home')->with([
            'top_menu'      => $this->getUserMenu(),
            'user'          => $user,
            'rank'          => $this->user->rank($user->id),
            'total_users'   => $this->user->countUsersWithReputation(),
            'reputation'    => $this->reputation->takeForUser($user->id),
            'skills'        => Skill::where('user_id', $user->id)->orderBy('order')->get()
        ]);
    }
}
