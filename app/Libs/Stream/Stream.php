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
    private $repository;

    /**
     * @param StreamRepositoryInterface $repository
     */
    public function __construct(StreamRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Add activity into the stream
     *
     * @param ObjectInterface $activity
     * @return $this
     */
    public function add(ObjectInterface $activity)
    {
        $this->repository->create($activity->build());
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
     * @param array $targets
     * @return array
     */
    public function take($limit, $offset = 0, $objects = [], $verbs = [], $targets = [])
    {
        $collection = $this->repository->take($limit, $offset, $objects, $verbs, $targets);
        return $this->decorate($collection);
    }

    /**
     * Find activities by object, id and actions (verbs)
     *
     * @param $objects
     * @param array $id
     * @param array $verbs
     * @return mixed
     */
    public function findByObject($objects, $id = [], $verbs = [])
    {
        return $this->repository->findByObject($objects, $id, $verbs);
    }
}
