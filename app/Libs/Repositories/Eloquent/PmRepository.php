<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PmRepositoryInterface;

class PmRepository extends Repository implements PmRepositoryInterface
{
    /**
     * @return \Coyote\Pm
     */
    public function model()
    {
        return 'Coyote\Pm';
    }
}
