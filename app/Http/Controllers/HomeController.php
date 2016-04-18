<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as Reputation;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Services\Stream\Stream;
use Cache; // @todo zmienic z facade na factory

class HomeController extends Controller
{
    public function index(Microblog $microblog, Reputation $reputation, Stream $stream, Topic $topic)
    {
        $viewers = app('session.viewers');

        start_measure('stream', 'Stream activities');
        // take last stream activity for forum
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
            }),
            'voted'                 => Cache::remember('homepage:voted', 30, function () use ($topic) {
                return $topic->voted();
            }),
            'interesting'           => $topic->interesting($this->userId),
        ]);
    }
}
