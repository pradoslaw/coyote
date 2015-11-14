<?php

namespace Coyote\Stream;

use Coyote\Repositories\Mongodb\StreamRepository;
use Coyote\Stream\Objects\ObjectInterface;

class Stream
{
    private $model;

    public function __construct(StreamRepository $model)
    {
        $this->model = $model;
    }

    public function add(ObjectInterface $activity)
    {
        $this->model->create($activity->build());
    }
}
