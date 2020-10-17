<?php

namespace Coyote\Listeners;

use Coyote\Events\TopicWasDeleted;
use Coyote\Events\TopicWasSaved;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Topic;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculateTagWeight implements ShouldQueue
{
    /**
     * @var TagRepository
     */
    private $tag;

    /**
     * @var TopicRepository
     */
    private $topic;

    /**
     * @param TagRepository $tag
     * @param TopicRepository $topic
     */
    public function __construct(TagRepository $tag, TopicRepository $topic)
    {
        $this->tag = $tag;
        $this->topic = $topic;
    }

    /**
     * Handle the event.
     *
     * @param  TopicWasSaved|TopicWasDeleted $event
     * @return void
     */
    public function handle($event)
    {
        $topic = $event instanceof TopicWasDeleted ? (new Topic)->forceFill($event->topic) : $event->topic;

        $ids = $topic->tags->pluck('id');

        $post = $topic->firstPost;

        if ($post->edit_count) {
            /** @var \Coyote\Post\Log $log */
            $log = $post->logs()->orderByDesc('id')->limit(1)->first();

            $ids = $ids->merge($this->tag->findWhere(['name' => $log->tags])->pluck('id'));
        }

        $this->tag->countTopics($ids->toArray());
    }
}
