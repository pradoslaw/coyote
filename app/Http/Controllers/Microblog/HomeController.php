<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Criteria\Microblog\OnlyMine;
use Coyote\Repositories\Criteria\Microblog\WithTag;

class HomeController extends Controller
{
    use CacheFactory;

    /**
     * @var MicroblogRepository
     */
    private $microblog;

    /**
     * @param MicroblogRepository $microblog
     */
    public function __construct(MicroblogRepository $microblog)
    {
        parent::__construct();

        $this->microblog = $microblog;
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
        $tags = $this->getCacheFactory()->remember('microblog:tags', 30, function () {
            return $this->microblog->getTags();
        });

        // we MUST NOT cache popular entries because it may contains current user's data
        $popular = $this->microblog->takePopular(5);

        $parser = ['main' => app('parser.microblog'), 'comment' => app('parser.microblog.comment')];

        foreach ($microblogs->items() as &$microblog) {
            /** @var \Coyote\Microblog $microblog */
            $microblog->text = $parser['main']->parse($microblog->text);

            foreach ($microblog->comments as &$comment) {
                $comment->html = $parser['comment']->parse($comment->text);
            }
        }

        return $this->view('microblog.home', [
            'count'                     => $this->microblog->count(),
            'count_user'                => $this->microblog->countForUser($this->userId),
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

        $this->microblog->pushCriteria(new OnlyMine($this->userId));
        return $this->index();
    }
}
