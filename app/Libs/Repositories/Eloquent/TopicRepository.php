<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\TopicRepositoryInterface;

class TopicRepository extends Repository implements TopicRepositoryInterface
{
    /**
     * @return \Coyote\Topic
     */
    public function model()
    {
        return 'Coyote\Topic';
    }
}
