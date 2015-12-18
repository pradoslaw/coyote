<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as Reputation;
use Coyote\Stream\Stream;
use Debugbar;
use Cache;

class HomeController extends Controller
{
    public function index(Microblog $microblog, Reputation $reputation, Stream $stream)
    {
        $microblog->setUserId(auth()->check() ? auth()->user()->id : null);
        $viewers = app('Session\Viewers');

        Debugbar::startMeasure('stream', 'Stream activities');
        // tymczasowo naglowki tylko dla mikroblogow, a nie dla forum
        $activities = $stream->take(10, 0, ['Topic', 'Post', 'Comment'], ['Create', 'Update']);
        Debugbar::stopMeasure('stream');

        return view('home', [
            'viewers'                   => $viewers->render(),
            'microblogs'                => $microblog->take(10),
            'activities'                => $activities,
            'reputation'                => Cache::remember('homepage:reputation', 30, function () use ($reputation) {
                return [
                    'month'      => $reputation->monthly(),
                    'year'       => $reputation->yearly(),
                    'total'      => $reputation->total()
                ];
            })
        ]);
    }
}
