<?php

namespace Coyote\Http\Controllers\Profile;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Controllers\User\UserMenuTrait;
use Coyote\Http\Forms\User\SkillsForm;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as ReputationRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\User;
use Illuminate\Http\Request;

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
     * @var PostRepository
     */
    private $post;

    /**
     * @param UserRepository $user
     * @param ReputationRepository $reputation
     * @param PostRepository $post
     */
    public function __construct(UserRepository $user, ReputationRepository $reputation, PostRepository $post)
    {
        parent::__construct();

        $this->user = $user;
        $this->reputation = $reputation;
        $this->post = $post;
    }

    /**
     * @param \Coyote\User $user
     * @param string $tab
     * @return \Illuminate\View\View
     */
    public function index($user, $tab = 'reputation')
    {
        $this->validateWith(
            $this->getValidationFactory()->make(['tab' => strtolower($tab)], ['tab' => 'in:history,reputation,post'])
        );

        $this->breadcrumb->push($user->name, route('profile', ['user' => $user->id]));
        $this->public['profile_history'] = route('profile.history', [$user->id]);

        $menu = $this->getUserMenu();

        if ($menu->get('profile')) {
            // activate "Profile" tab no matter what.
            $menu->get('profile')->activate();
        }

        return $this->view('profile.home')->with([
            'top_menu'      => $menu,
            'user'          => $user,
            'skills'        => $user->skills()->orderBy('order')->get(),
            'rate_labels'   => SkillsForm::RATE_LABELS,
            'tab'           => strtolower($tab),
            'module'        => $this->$tab($user)
        ]);
    }

    /**
     * Run the validation routine against the given validator.
     *
     * @param  \Illuminate\Contracts\Validation\Validator|array  $validator
     * @param  \Illuminate\Http\Request|null  $request
     * @return void
     */
    public function validateWith($validator, Request $request = null)
    {
        if ($validator->fails()) {
            abort(404);
        }
    }

    /**
     * @param $user
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function history($user, Request $request)
    {
        return view('profile.partials.reputation_list', [
            'reputation' => $this->reputation->history($user->id, $request->input('offset'))
        ]);
    }

    /**
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function reputation(User $user)
    {
        return view('profile.partials.reputation', [
            'user'          => $user,
            'rank'          => $this->user->rank($user->id),
            'total_users'   => $this->user->countUsersWithReputation(),
            'reputation'    => $this->reputation->history($user->id),
            'chart'         => $this->reputation->chart($user->id),
        ]);
    }

    /**
     * Singular name of method because of backward compatibility.
     *
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function post(User $user)
    {
        $this->post->pushCriteria(new OnlyThoseWithAccess(auth()->user()));

        return view('profile.partials.posts', [
            'user'          => $user,
            'pie'           => $this->post->pieChart($user->id),
            'line'          => $this->post->lineChart($user->id),
            'comments'      => $this->post->countComments($user->id),
            'given_votes'   => $this->post->countGivenVotes($user->id),
            'received_votes'=> $this->post->countReceivedVotes($user->id),
        ]);
    }
}
