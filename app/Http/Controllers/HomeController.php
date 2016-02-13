<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as Reputation;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Stream\Stream;
use Cache;

class HomeController extends Controller
{
    public function index(Microblog $microblog, Reputation $reputation, Stream $stream, Topic $topic)
    {
        $viewers = app()->make('viewers');

        start_measure('stream', 'Stream activities');
        // tymczasowo naglowki tylko dla mikroblogow, a nie dla forum
        $activities = $stream->take(10, 0, ['Topic', 'Post', 'Comment'], ['Create', 'Update'], ['Forum', 'Post', 'Topic']);
        stop_measure('stream');

        $topic->pushCriteria(new OnlyThoseWithAccess());

        return $this->view('home', [
            'viewers'              => $viewers->render(),
            'microblogs'           => $microblog->take(10),
            'activities'           => $activities,
            'reputation'           => Cache::remember('homepage:reputation', 30, function () use ($reputation) {
                return [
                    'month'   => $reputation->monthly(),
                    'year'    => $reputation->yearly(),
                    'total'   => $reputation->total()
                ];
            }),
            'settings'             => $this->getSettings(),
            'newest'               => Cache::remember('homepage:newest', 30, function () use ($topic) {
                return $topic->newest();
            })
        ]);
    }
}
