<?php

namespace Coyote\Repositories\Eloquent\Post;

use Coyote\Repositories\Contracts\Post\VoteRepositoryInterface;
use Coyote\Repositories\Eloquent\Repository;

class VoteRepository extends Repository implements VoteRepositoryInterface
{
    /**
     * @return \Coyote\Post\Vote
     */
    public function model()
    {
        return 'Coyote\Post\Vote';
    }
}
