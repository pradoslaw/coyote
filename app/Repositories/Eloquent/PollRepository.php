<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PollRepositoryInterface;

class PollRepository extends Repository implements PollRepositoryInterface
{
    /**
     * @return \Coyote\Poll
     */
    public function model()
    {
        return 'Coyote\Poll';
    }
}
