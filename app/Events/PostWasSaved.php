<?php

namespace Coyote\Events;

use Coyote\Post;
use Illuminate\Queue\SerializesModels;

class PostWasSaved extends Event
{
    use SerializesModels;

    public $post;

    /**
     * Create a new event instance.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
