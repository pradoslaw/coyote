<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Eloquent\MicroblogRepository;
use Cache;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index(MicroblogRepository $repository)
    {
        $this->breadcrumb->push('Mikroblog', route('microblog.home'));

        $microblogs = $repository->paginate(25);

        // tagi nie zmieniaja sie czesto, wiec mozemy wrzucic do cache na 30 min
        $tags = Cache::remember('microblogs-tags', 30, function () use ($repository) {
            return $repository->getTags();
        });

        $popular = $repository->takePopular(5);

        return parent::view('microblog.home', [
            'total'                     => $microblogs->total(),
            'pagination'                => $microblogs->render(),
            'microblogs'                => $microblogs->items()
        ])->with(compact('tags', 'popular'));
    }
}
