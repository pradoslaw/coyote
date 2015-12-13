<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Cache;

class TopicRepository extends Repository implements WordRepositoryInterface
{
    /**
     * @return \Coyote\Topic
     */
    public function model()
    {
        return 'Coyote\Topic';
    }
}
