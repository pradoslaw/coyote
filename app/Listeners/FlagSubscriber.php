<?php

namespace Coyote\Listeners;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\PostWasDeleted;
use Coyote\Events\TopicWasDeleted;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\Repositories\Contracts\FlagRepositoryInterface as FlagRepository;
use Coyote\Topic;
use Illuminate\Contracts\Queue\ShouldQueue;

class FlagSubscriber implements ShouldQueue
{
    private $repository;

    public function __construct(FlagRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handleTopicDelete(TopicWasDeleted $event)
    {
        $this->repository->deleteByModel(Topic::class, $event->topic['id']);
    }

    public function handlePostDelete(PostWasDeleted $event)
    {
        $this->repository->deleteByModel(Post::class, $event->post['id']);
    }

    public function handleMicroblogDelete(MicroblogWasDeleted $event)
    {
        $this->repository->deleteByModel(Microblog::class, $event->microblog['id']);
    }

    public function subscribe($events)
    {
        $events->listen(
            TopicWasDeleted::class,
            'Coyote\Listeners\FlagSubscriber@handleTopicDelete'
        );

        $events->listen(
            PostWasDeleted::class,
            'Coyote\Listeners\FlagSubscriber@handlePostDelete'
        );

        $events->listen(
            MicroblogWasDeleted::class,
            'Coyote\Listeners\FlagSubscriber@handleMicroblogDelete'
        );
    }
}
