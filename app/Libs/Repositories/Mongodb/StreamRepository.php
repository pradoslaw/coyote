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

    /**
     * Take X last activities
     *
     * @param $limit
     * @param int $offset
     * @param array $objects
     * @param array $verbs
     * @return mixed
     */
    public function take($limit, $offset = 0, $objects = [], $verbs = [])
    {
        $result = $this->model
                ->orderBy('_id', 'DESC')
                ->offset($offset)
                ->take($limit);

        if ($objects) {
            $result->whereIn('object.objectType', $this->toArray($objects));
        }

        if ($verbs) {
            $result->whereIn('verb', $this->toArray($verbs));
        }

        return $result->get();
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
        $result = $this->model->whereIn('object.objectType', $this->toArray($objects));

        if ($id) {
            if (!is_array($id)) {
                $id = [$id];
            }
            $result->whereIn('object.id', $id);
        }

        if ($verbs) {
            $result->whereIn('verb', $this->toArray($verbs));
        }

        return $result->get();
    }

    /**
     * Transform string to array and converts to lower case
     *
     * @param $object
     * @return array
     */
    private function toArray($object)
    {
        if (!is_array($object)) {
            $object = [$object];
        }

        return array_map(function ($item) {
            return strtolower(class_basename($item));
        }, $object);
    }
}
