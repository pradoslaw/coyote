<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Eloquent\MicroblogRepository;
use Image;
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

        foreach ($microblogs as &$microblog) {
            if (isset($microblog['comments'])) {
                $microblog['comments_count'] = count($microblog['comments']);
                $microblog['comments'] = array_slice($microblog['comments'], -2);
            }
        }

        // tagi nie zmieniaja sie czesto, wiec mozemy wrzucic do cache na 30 min
        $tags = Cache::remember('microblogs-tags', 30, function () use ($repository) {
            return $repository->getTags();
        });

        $popular = $repository->takePopular(5);

        return parent::view('microblog.home', [
            'total'                     => $microblogs->total(),
            'pagination'                => $microblogs->render(),
            'microblogs'                => $microblogs->toArray()['data']
        ])->with(compact('tags', 'popular'));
    }
}
