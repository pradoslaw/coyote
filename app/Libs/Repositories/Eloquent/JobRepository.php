<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\JobRepositoryInterface;

class JobRepository extends Repository implements JobRepositoryInterface
{
    /**
     * @return \Coyote\Job
     */
    public function model()
    {
        return 'Coyote\Job';
    }
}
