<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\FirmRepositoryInterface;

class FirmRepository extends Repository implements FirmRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Firm';
    }
}
