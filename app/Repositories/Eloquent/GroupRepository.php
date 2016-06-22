<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\GroupRepositoryInterface;

class GroupRepository extends Repository implements GroupRepositoryInterface
{
    /**
     * @return \Coyote\Group
     */
    public function model()
    {
        return 'Coyote\Group';
    }
}
