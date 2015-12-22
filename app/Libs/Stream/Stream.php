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
    /**
     * @var StreamRepositoryInterface
     */
    private $model;

    /**
     * @param StreamRepositoryInterface $model
     */
    public function __construct(StreamRepositoryInterface $model)
    {
        $this->model = $model;
    }

    /**
     * @param ObjectInterface $activity
     * @return $this
     */
    public function add(ObjectInterface $activity)
    {
        $this->model->create($activity->build());
        return $this;
    }

    /**
     * @param mixed $collection
     * @return array
     */
    public function decorate($collection)
    {
        $result = [];

        foreach ($collection as $index => $row) {
            $class = __NAMESPACE__ . '\\Render\\' . ucfirst(camel_case($row['object.objectType']));
            $decorator = new $class($row);

            $result[$index] = $decorator->render();
        }

        return $result;
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
        $collection = $this->model->take($limit, $offset, $objects, $verbs);
        return $this->decorate($collection);
    }
}
