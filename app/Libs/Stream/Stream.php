<?php

namespace Coyote\Stream;

use Coyote\Repositories\Contracts\StreamRepositoryInterface;
use Coyote\Stream\Objects\ObjectInterface;
use Coyote\Stream\Objects;

/**
 * Class Stream
 * @package Coyote\Stream
 */
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

    /**
     * @param $limit
     * @param int $offset
     * @param array $objects
     * @param array $verbs
     * @return array
     */
    public function take($limit, $offset = 0, $objects = [], $verbs = [])
    {
        $data = $this->model->take($limit, $offset, $objects, $verbs);
        $result = [];

        foreach ($data as $row) {
            $class = __NAMESPACE__ . '\\Render\\' . ucfirst(camel_case($row['object.objectType']));
            $decorator = new $class($row);

            $result[] = $decorator->render();
        }

        return $result;
    }
}
