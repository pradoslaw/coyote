<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as ReputationRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Services\Session\Viewers;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;
use Coyote\Services\Stream\Renderer;

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
     * @var StreamRepository
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
     * @var ForumRepository
     */
    protected $forum;

    /**
     * @param MicroblogRepository $microblog
     * @param ReputationRepository $reputation
     * @param StreamRepository $stream
     * @param TopicRepository $topic
     * @param WikiRepository $wiki
     * @param ForumRepository $forum
     */
    public function __construct(
        MicroblogRepository $microblog,
        ReputationRepository $reputation,
        StreamRepository $stream,
        TopicRepository $topic,
        WikiRepository $wiki,
        ForumRepository $forum
    ) {
        parent::__construct();

        $this->microblog = $microblog;
        $this->reputation = $reputation;
        $this->stream = $stream;
        $this->topic = $topic;
        $this->wiki = $wiki;
        $this->forum = $forum;
    }

    /**
     * @return \Illuminate\View\View
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

                if (in_array($name, ['reputation', 'newest', 'voted', 'interesting', 'blog', 'patronage'])) {
                    $result[$name] = $cache->remember('homepage:' . $name, 30, function () use ($method) {
                        return $this->$method();
                    });
                } else {
                    $result[$name] = $this->$method();
                }
            }
        }

        $this->public['settings_url'] = route('user.settings.ajax', [], false);

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
        $parent = $this->wiki->findByPath('Blog');
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
        return  $this->microblog->take(5);
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
        return $this->topic->interesting();
    }

    /**
     * @return array
     */
    private function getActivities()
    {
        // take last stream activity for forum
        return (new Renderer($this->stream->forumFeeds($this->forum->getRestricted())))->render();
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

    /**
     * @return array
     */
    private function getPatronage()
    {
        /** @var \Coyote\Wiki $parent */
        $parent = $this->wiki->findByPath('Patronat');
        if (!$parent) {
            return [];
        }

        return $parent->children()->latest()->limit(1)->first(['path', 'title', 'excerpt']);
    }
}
