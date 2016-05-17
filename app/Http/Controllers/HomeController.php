<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as ReputationRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Services\Session\Viewers;
use Coyote\Services\Stream\Stream;

class HomeController extends Controller
{
    /**
     * @var MicroblogRepository
     */
    protected $microblog;

    /**
     * @var ReputationRepository
     */
    protected $reputation;

    /**
     * @var Stream
     */
    protected $stream;

    /**
     * @var TopicRepository
     */
    protected $topic;

    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @param MicroblogRepository $microblog
     * @param ReputationRepository $reputation
     * @param Stream $stream
     * @param TopicRepository $topic
     * @param WikiRepository $wiki
     */
    public function __construct(
        MicroblogRepository $microblog,
        ReputationRepository $reputation,
        Stream $stream,
        TopicRepository $topic,
        WikiRepository $wiki
    ) {
        parent::__construct();

        $this->microblog = $microblog;
        $this->reputation = $reputation;
        $this->stream = $stream;
        $this->topic = $topic;
        $this->wiki = $wiki;
    }

    /**
     * @return $this
     */
    public function index()
    {
        $result = [];
        $reflection = new \ReflectionClass($this);

        $cache = $this->getCacheFactory();

        $this->topic->pushCriteria(new OnlyThoseWithAccess());

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PRIVATE) as $method) {
            $method = $method->name;
            $snake = snake_case($method);

            if (substr($snake, 0, 3) === 'get') {
                $name = substr($snake, 4);

                if (in_array($name, ['reputation', 'newest', 'voted', 'blog'])) {
                    $result[$name] = $cache->remember('homepage:' . $name, 30, function () use ($method) {
                        return $this->$method();
                    });
                } else {
                    $result[$name] = $this->$method();
                }
            }
        }

        return $this->view('home', $result)->with('settings', $this->getSettings());
    }

    /**
     * @return array
     */
    private function getReputation()
    {
        return [
            'month'   => $this->reputation->monthly(),
            'year'    => $this->reputation->yearly(),
            'total'   => $this->reputation->total()
        ];
    }

    /**
     * @return mixed
     */
    private function getBlog()
    {
        /** @var \Coyote\Wiki $parent */
        $parent = $this->wiki->findBy('path', 'Blog', ['id', 'parent_id']);
        if (!$parent) {
            return [];
        }

        return $parent->children()->latest()->limit(5)->get(['created_at', 'path', 'title', 'long_title']);
    }

    /**
     * @return mixed
     */
    private function getMicroblogs()
    {
        return $this->microblog->take(10);
    }

    /**
     * @return mixed
     */
    private function getVoted()
    {
        return $this->topic->voted();
    }

    /**
     * @return mixed
     */
    private function getNewest()
    {
        return $this->topic->newest();
    }

    /**
     * @return mixed
     */
    private function getInteresting()
    {
        return $this->topic->interesting($this->userId);
    }

    /**
     * @return array
     */
    private function getActivities()
    {
        // take last stream activity for forum
        return $this->stream->take(
            10, // limit
            0,
            ['Topic', 'Post', 'Comment'], // objects
            ['Create', 'Update'], // actions
            ['Forum', 'Post', 'Topic'] // targets
        );
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function getViewers()
    {
        /** @var Viewers $viewers */
        $viewers = app(Viewers::class);
        return $viewers->render();
    }
}
