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

    public function take($limit, $offset = 0, $objects = [], $verbs = [])
    {
        $result = $this->model
                ->orderBy('_id', 'DESC')
                ->offset($offset)
                ->take($limit);

        $toArray = function ($object) {
            return array_map(function ($item) {
                return strtolower(class_basename($item));
            }, $object);
        };

        if ($objects) {
            $result->whereIn('object.objectType', $toArray($objects));
        }

        if ($verbs) {
            $result->whereIn('verb', $toArray($verbs));
        }

        return $result->get();
    }
}
