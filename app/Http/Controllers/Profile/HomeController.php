<?php

namespace Coyote\Http\Controllers\Profile;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Controllers\User\UserMenuTrait;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as Reputation;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;

class HomeController extends Controller
{
    use UserMenuTrait;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Reputation
     */
    private $reputation;

    /**
     * HomeController constructor.
     *
     * @param User $user

     * @param Reputation $reputation
     */
    public function __construct(User $user, Reputation $reputation)
    {
        parent::__construct();

        $this->user = $user;
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
            'skills'        => $user->skills()->orderBy('order')->get()
        ]);
    }
}
