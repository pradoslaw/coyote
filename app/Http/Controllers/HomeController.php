<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as Session;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request, Session $session, Microblog $microblog)
    {
        $viewers = new \Coyote\Session\Viewers($session, $request);

        $microblogs = $microblog->take(10);

        return view('home', [
            'viewers'                   => $viewers->render(),
            'microblogs'                => $microblogs->all()
        ]);
    }
}
