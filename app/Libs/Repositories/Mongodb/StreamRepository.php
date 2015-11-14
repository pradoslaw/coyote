<?php

namespace Coyote\Repositories\Mongodb;

use Coyote\Repositories\Eloquent\Repository;

class StreamRepository extends Repository
{
    public function model()
    {
        return 'Coyote\Stream';
    }
}
