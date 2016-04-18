<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as Reputation;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Services\Session\Viewers;
use Coyote\Services\Stream\Stream;

class HomeController extends Controller
{
    use CacheFactory;

    public function index(Microblog $microblog, Reputation $reputation, Stream $stream, Topic $topic)
    {
        $viewers = $this->getSessionViewersFactory();

        start_measure('stream', 'Stream activities');
        // take last stream activity for forum
        $activities = $stream->take(10, 0, ['Topic', 'Post', 'Comment'], ['Create', 'Update'], ['Forum', 'Post', 'Topic']);
        stop_measure('stream');

        $topic->pushCriteria(new OnlyThoseWithAccess());
        $cache = $this->getCacheFactory();

        return $this->view('home', [
            'viewers'              => $viewers->render(),
            'microblogs'           => $microblog->take(10),
            'activities'           => $activities,
            'reputation'           => $cache->remember('homepage:reputation', 30, function () use ($reputation) {
                return [
                    'month'   => $reputation->monthly(),
                    'year'    => $reputation->yearly(),
                    'total'   => $reputation->total()
                ];
            }),
            'settings'             => $this->getSettings(),
            'newest'               => $cache->remember('homepage:newest', 30, function () use ($topic) {
                return $topic->newest();
            }),
            'voted'                 => $cache->remember('homepage:voted', 30, function () use ($topic) {
                return $topic->voted();
            }),
            'interesting'           => $topic->interesting($this->userId),
        ]);
    }

    /**
     * @return Viewers
     */
    private function getSessionViewersFactory()
    {
        return app(Viewers::class);
    }
}
