<?php

namespace Coyote\Events;

use Coyote\Post\Comment;
use Illuminate\Queue\SerializesModels;

class CommentDeleted
{
    use SerializesModels;

    /**
     * @var array
     */
    public $comment;

    /**
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = array_only($comment->toArray(), ['id']);
    }
}
