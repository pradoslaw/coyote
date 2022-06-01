<?php

namespace Coyote\Listeners;

use Coyote\Events\PostWasDeleted;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Services\Elasticsearch\Crawler;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostListener implements ShouldQueue
{
    /**
     * @var PostRepository
     */
    protected $post;

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * @param PostRepository $post
     */
    public function __construct(PostRepository $post)
    {
        $this->post = $post;
        $this->crawler = new Crawler();
    }

    /**
     * @param PostWasDeleted $event
     * @throws \Exception
     */
    public function onPostDelete(PostWasDeleted $event)
    {
        /** @var \Coyote\Post $post */
        $post = $this->post->withTrashed()->find($event->post['id']);

        $this->crawler->delete($post);
        // reindex whole topic
        $this->crawler->index($post->topic);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            PostWasDeleted::class,
            [__CLASS__, 'onPostDelete']
        );
    }
}
