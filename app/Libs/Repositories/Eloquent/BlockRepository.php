<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\BlockRepositoryInterface;

class BlockRepository extends Repository implements BlockRepositoryInterface
{
    /**
     * @return \Coyote\Block
     */
    public function model()
    {
        return 'Coyote\Block';
    }
}
