<?php

namespace Coyote\Http\Controllers\Profile;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Controllers\User\UserMenuTrait;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as ReputationRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;

class HomeController extends Controller
{
    use UserMenuTrait;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @var ReputationRepository
     */
    private $reputation;

    /**
     * HomeController constructor.
     *
     * @param UserRepository $user
     * @param ReputationRepository $reputation
     */
    public function __construct(UserRepository $user, ReputationRepository $reputation)
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
            'chart'         => $this->reputation->chart($user->id),
            'skills'        => $user->skills()->orderBy('order')->get()
        ]);
    }
}
