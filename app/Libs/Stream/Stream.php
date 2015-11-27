<?php

namespace Coyote\Stream;

use Coyote\Repositories\Contracts\StreamRepositoryInterface;
use Coyote\Stream\Objects\ObjectInterface;

class Stream
{
    private $model;

    public function __construct(StreamRepositoryInterface $model)
    {
        $this->model = $model;
    }

    public function add(ObjectInterface $activity)
    {
        $this->model->create($activity->build());
        return $this;
    }
}
