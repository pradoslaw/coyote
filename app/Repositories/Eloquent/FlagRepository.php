<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\FlagRepositoryInterface;

class FlagRepository extends Repository implements FlagRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Flag';
    }
}
