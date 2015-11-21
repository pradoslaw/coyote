<?php

namespace Coyote\Http\Controllers;

use Illuminate\Http\Request;
use Coyote\Repositories\Eloquent\MicroblogRepository;

class HomeController extends Controller
{
    /**
     * @param Request $request
     * @param MicroblogRepository $microblog
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, MicroblogRepository $microblog)
    {
        $viewers = new \Coyote\Session\Viewers(new \Coyote\Session(), $request);

        $microblogs = $microblog->take(10);
//dd($microblogs->all());
        return view('home', [
            'viewers'                   => $viewers->render(),
            'microblogs'                => $microblogs->all()
        ]);
    }
}
