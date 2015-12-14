<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PostRepositoryInterface;

class PostRepository extends Repository implements PostRepositoryInterface
{
    /**
     * @return \Coyote\Topic
     */
    public function model()
    {
        return 'Coyote\Post';
    }
}
