<?php

namespace Coyote\Repositories\Mongodb;

use Coyote\Repositories\Contracts\StreamRepositoryInterface;
use Coyote\Repositories\Eloquent\Repository;

class StreamRepository extends Repository implements StreamRepositoryInterface
{
    public function model()
    {
        return 'Coyote\Stream';
    }
}
