<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Repositories\Criteria\Microblog\OnlyMine;
use Coyote\Repositories\Criteria\Microblog\WithTag;
use Cache;

class HomeController extends Controller
{
    /**
     * @var MicroblogRepositoryInterface
     */
    private $microblog;

    public function __construct(MicroblogRepositoryInterface $repository)
    {
        parent::__construct();

        $this->microblog = $repository;
        $this->microblog->setUserId(auth()->check() ? auth()->user()->id : null);
        $this->breadcrumb->push('Mikroblog', route('microblog.home'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $microblogs = $this->microblog->paginate(10);
        $this->microblog->resetCriteria();

        // let's cache microblog tags. we don't need to run this query every time
        $tags = Cache::remember('microblog:tags', 30, function () {
            return $this->microblog->getTags();
        });

        // we MUST NOT cache popular entries because it may contains current user's data
        $popular = $this->microblog->takePopular(5);

        $parser = ['main' => app()->make('Parser\Microblog'), 'comment' => app()->make('Parser\Comment')];

        foreach ($microblogs->items() as &$microblog) {
            $microblog->text = $parser['main']->parse($microblog->text);

            foreach ($microblog->comments as &$comment) {
                $comment->text = $parser['comment']->parse($comment->text);
            }
        }

        return parent::view('microblog.home', [
            'count'                     => $this->microblog->count(),
            'count_user'                => $this->microblog->countForUser(),
            'pagination'                => $microblogs->render(),
            'microblogs'                => $microblogs->items(),
            'route'                     => request()->route()->getName()
        ])->with(compact('tags', 'popular'));
    }

    /**
     * @param string $tag
     * @return \Illuminate\View\View
     */
    public function tag($tag)
    {
        $this->breadcrumb->push('Wpisy z tagiem: ' . $tag, route('microblog.tag', [$tag]));

        $this->microblog->pushCriteria(new WithTag($tag));
        return $this->index();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function mine()
    {
        $this->breadcrumb->push('Moje wpisy', route('microblog.mine'));

        $this->microblog->pushCriteria(new OnlyMine());
        return $this->index();
    }
}
