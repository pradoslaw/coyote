<?php

namespace Coyote\Repositories\Eloquent\Post;

use Coyote\Repositories\Contracts\Post\CommentRepositoryInterface;
use Coyote\Repositories\Eloquent\Repository;

class CommentRepository extends Repository implements CommentRepositoryInterface
{
    /**
     * @return \Coyote\Post\Comment
     */
    public function model()
    {
        return 'Coyote\Post\Comment';
    }
}
