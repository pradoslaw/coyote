<?php

namespace Coyote\Events;

use Coyote\Events\Event;
use Coyote\Post;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PostWasDeleted extends Event
{
    use SerializesModels;

    /**
     * @var array
     */
    public $post;

    /**
     * Create a new event instance.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post->toArray();
    }
}
