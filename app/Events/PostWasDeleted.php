<?php

namespace Coyote\Events;

use Coyote\Post;
use Illuminate\Queue\SerializesModels;

class PostWasDeleted
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
        $this->post = array_only($post->toArray(), ['id']);
    }
}
