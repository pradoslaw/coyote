<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Stream\Stream;

class HomeController extends Controller
{
    public function index(Microblog $microblog, Stream $stream)
    {
        $viewers = app('Session\Viewers');

        // tymczasowo naglowki tylko dla mikroblogow, a nie dla forum
        $activities = $stream->take(10, 0, ['Microblog', 'Comment'], ['Create', 'Update']);
        $microblogs = $microblog->take(10);

        return view('home', [
            'viewers'                   => $viewers->render(),
            'microblogs'                => $microblogs->all(),
            'activities'                => $activities
        ]);
    }
}
