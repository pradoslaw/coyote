<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Eloquent\MicroblogRepository;
use Image;

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

        return parent::view('microblog.home', [
            'pagination'                => $microblogs->render(),
            'microblogs'                => $microblogs->toArray()['data']
        ]);
    }
}
