<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\ForumRepositoryInterface;

class ForumRepository extends Repository implements ForumRepositoryInterface
{
    /**
     * @return \Coyote\Forum
     */
    public function model()
    {
        return 'Coyote\Forum';
    }
}
