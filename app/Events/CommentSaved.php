<?php

namespace Coyote\Events;

use Coyote\Post\Comment;
use Illuminate\Queue\SerializesModels;

class CommentSaved
{
    use SerializesModels;

    /**
     * @var Comment
     */
    public $comment;

    /**
     * CommentSaved constructor.
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
}
