<?php

namespace Coyote\Listeners;

ini_set('memory_limit', '10G');
set_time_limit(0);

use Coyote\Events\TopicDeleted;
use Coyote\Events\TopicMoved;
use Coyote\Events\TopicSaved;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Services\Elasticsearch\Crawler;
use Coyote\Topic;
use Illuminate\Contracts\Queue\ShouldQueue;

class TopicListener implements ShouldQueue
{
    /**
     * @var TopicRepository
     */
    protected $topic;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @param TopicRepository $topic
     * @param Crawler $crawler
     */
    public function __construct(TopicRepository $topic, Crawler $crawler)
    {
        $this->topic = $topic;
        $this->crawler = $crawler;
    }

    /**
     * @param TopicSaved $event
     */
    public function onTopicSave(TopicSaved $event)
    {
        $this->crawler->index($event->topic);
    }

    /**
     * @param TopicMoved $event
     */
    public function onTopicMove(TopicMoved $event)
    {
        $this->crawler->index($event->topic);
    }

    /**
     * @param TopicDeleted $event
     * @throws \Exception
     */
    public function onTopicDelete(TopicDeleted $event)
    {
        $topic = (new Topic)->forceFill($event->topic);

        $this->crawler->delete($topic);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\TopicSaved',
            'Coyote\Listeners\TopicListener@onTopicSave'
        );

        $events->listen(
            'Coyote\Events\TopicMoved',
            'Coyote\Listeners\TopicListener@onTopicMove'
        );

        $events->listen(
            'Coyote\Events\TopicDeleted',
            'Coyote\Listeners\TopicListener@onTopicDelete'
        );
    }
}
