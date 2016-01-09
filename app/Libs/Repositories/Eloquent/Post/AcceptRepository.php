<?php

namespace Coyote\Repositories\Eloquent\Post;

use Coyote\Repositories\Contracts\Post\AcceptRepositoryInterface;
use Coyote\Repositories\Eloquent\Repository;

class AcceptRepository extends Repository implements AcceptRepositoryInterface
{
    /**
     * @return \Coyote\Post\Accept
     */
    public function model()
    {
        return 'Coyote\Post\Accept';
    }
}
