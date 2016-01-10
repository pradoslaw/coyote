<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as Reputation;
use Coyote\Repositories\Contracts\SettingRepositoryInterface as Setting;
use Coyote\Stream\Stream;
use Debugbar;
use Cache;

class HomeController extends Controller
{
    public function index(Microblog $microblog, Reputation $reputation, Stream $stream, Setting $setting)
    {
        $microblog->setUserId(auth()->id());
        $viewers = app('Session\Viewers');

        Debugbar::startMeasure('stream', 'Stream activities');
        // tymczasowo naglowki tylko dla mikroblogow, a nie dla forum
        $activities = $stream->take(10, 0, ['Topic', 'Post', 'Comment'], ['Create', 'Update'], ['Forum', 'Post', 'Topic']);
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
            }),
            'settings'                  => $setting->getAll(auth()->id(), request()->session()->getId())
        ]);
    }
}
