<?php
namespace Coyote\Http\Controllers\Profile;

use Coyote\Domain\View\Chart;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\SkillsRequest;
use Coyote\Http\Resources\MicroblogCollection;
use Coyote\Http\Resources\TagResource;
use Coyote\Http\Resources\UserResource;
use Coyote\Repositories\Eloquent\MicroblogRepository;
use Coyote\Repositories\Eloquent\ReputationRepository;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\Microblogs\Builder;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private UserRepository       $user,
        private ReputationRepository $reputation,
        private MicroblogRepository  $microblog,
    )
    {
        parent::__construct();

        $this->middleware(function (Request $request, $next) {
            /** @var User $user */
            $user = $request->route('user_trashed');
            abort_if($user->deleted_at && (!$this->userId || $this->auth->cannot('adm-access')), 404);
            return $next($request);
        });
    }

    public function index(User $user): View
    {
        $this->breadcrumb->push($user->name, route('profile', ['user_trashed' => $user->id]));

        return $this->view('profile.home')->with([
            'user'               => new UserResource($user),
            'skills'             => TagResource::collection($user->skills->load('category')),
            'rate_labels'        => SkillsRequest::RATE_LABELS,
            'tab'                => 'microblog',
            'chartLibraryScript' => Chart::librarySourceHtml(),
            'microblogModule'    => $this->microblog($user),
            'reputationModule'   => $this->reputation($user),
            'popular_tags'       => $this->microblog->popularTags($this->userId),
            'emojis'             => Emoji::all(),
        ]);
    }

    private function microblog(User $user): View
    {
        /** @var Builder $builder */
        $builder = app(Builder::class);
        $paginator = $builder->orderById()->onlyUsers($user)->paginate();
        return view('profile.partials.microblog', [
            'user'       => $user,
            'pagination' => new MicroblogCollection($paginator),
            'emojis'     => Emoji::all(),
        ]);
    }

    private function reputation(User $user): View
    {
        return view('profile.partials.reputation', [
            'user'        => $user,
            'rank'        => $this->user->rank($user->id),
            'total_users' => $this->user->countUsersWithReputation(),
            'reputation'  => $this->reputation->history($user->id),
            'chart'       => $this->chart($user),
        ]);
    }

    private function chart(User $user): Chart
    {
        $chart = $this->reputation->chart($user->id);
        return new Chart(
            \array_map(fn(array $item) => $item['label'], $chart),
            \array_map(fn(array $rec) => $rec['value'], $chart),
            ['#ff9f40'],
            'reputation-chart',
        );
    }

    public function history(User $user, Request $request): View
    {
        return view('profile.partials.reputation_list', [
            'reputation' => $this->reputation->history($user->id, $request->input('offset')),
        ]);
    }
}
