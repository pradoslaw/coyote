<?php

namespace Coyote\Http\Controllers\Profile;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Controllers\User\UserMenuTrait;
use Coyote\Http\Requests\SkillsRequest;
use Coyote\Http\Resources\MicroblogCollection;
use Coyote\Http\Resources\TagResource;
use Coyote\Http\Resources\UserResource;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as ReputationRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Repositories\Eloquent\MicroblogRepository;
use Coyote\Services\Microblogs\Builder;
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
     * @var MicroblogRepository
     */
    private $microblog;

    /**
     * @param UserRepository $user
     * @param ReputationRepository $reputation
     * @param PostRepository $post
     * @param MicroblogRepository $microblog
     */
    public function __construct(
        UserRepository $user,
        ReputationRepository $reputation,
        PostRepository $post,
        MicroblogRepository $microblog
    ) {
        parent::__construct();

        $this->user = $user;
        $this->reputation = $reputation;
        $this->post = $post;
        $this->microblog = $microblog;

        $this->middleware(function (Request $request, $next) {
            /** @var User $user */
            $user = $request->route('user_trashed');

            abort_if($user->deleted_at && (!$this->userId || $this->auth->cannot('adm-access')), 404);

            return $next($request);
        });
    }

    /**
     * @param \Coyote\User $user
     * @param string $tab
     * @return \Illuminate\View\View
     */
    public function index($user, $tab = 'reputation')
    {
        $this->breadcrumb->push($user->name, route('profile', ['user_trashed' => $user->id]));

        $menu = $this->getUserMenu();

        if ($menu->get('profile')) {
            // activate "Profile" tab no matter what.
            $menu->get('profile')->activate();
        }

        return $this->view('profile.home')->with([
            'top_menu'      => $menu,
            'user'          => new UserResource($user),
            'skills'        => TagResource::collection($user->skills->load('category')),
            'rate_labels'   => SkillsRequest::RATE_LABELS,
            'tab'           => strtolower($tab),
            'module'        => $this->$tab($user)
        ]);
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

        $pieChart = $this->post->pieChart($user->id);

        return view('profile.partials.posts', [
            'user'          => $user,
            'pie'           => $pieChart,
            'posts'         => array_sum($pieChart),
            'line'          => $this->post->lineChart($user->id),
            'comments'      => $this->post->countComments($user->id),
            'given_votes'   => $this->post->countGivenVotes($user->id),
            'received_votes'=> $this->post->countReceivedVotes($user->id),
        ]);
    }

    /**
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function microblog(User $user)
    {
        /** @var Builder $builder */
        $builder = app(Builder::class);

        $paginator = $builder->orderById()->onlyUsers($user)->paginate();

        return view('profile.partials.microblog', [
            'user'          => $user,
            'pagination'    => new MicroblogCollection($paginator)
        ]);
    }
}
