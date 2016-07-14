<?php

namespace Coyote\Repositories\Mongodb;

use Coyote\Http\Forms\Adm\StreamFilterForm;
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
     * @param array $targets
     * @return mixed
     */
    public function take($limit, $offset = 0, $objects = [], $verbs = [], $targets = [])
    {
        $result = $this->model
                ->orderBy('_id', 'DESC')
                ->offset($offset)
                ->take($limit);

        if (!empty($objects)) {
            $result->whereIn('object.objectType', $this->toArray($objects));
        }

        if (!empty($verbs)) {
            $result->whereIn('verb', $this->toArray($verbs));
        }

        if (!empty($targets)) {
            $result->whereIn('target.objectType', $this->toArray($targets));
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

        if (!empty($id)) {
            if (!is_array($id)) {
                $id = [$id];
            }
            $result->whereIn('object.id', $id);
        }

        if (!empty($verbs)) {
            $result->whereIn('verb', $this->toArray($verbs));
        }

        return $result->get();
    }

    /**
     * @param int $topicId
     * @return mixed
     */
    public function takeForTopic($topicId)
    {
        return $this
            ->model
            ->whereNested(function ($query) use ($topicId) {
                $query->where('target.objectType', 'topic')
                    ->where('target.id', $topicId);
            })
            ->whereNested(function ($query) use ($topicId) {
                $query->where('object.objectType', 'topic')
                    ->where('object.id', $topicId);
            }, 'or')
            ->orderBy('_id', 'DESC')
            ->paginate();
    }

    /**
     * @param StreamFilterForm $form
     * @return mixed
     */
    public function filter(StreamFilterForm $form)
    {
        $sql = $this->model;

        foreach ($form->getFields() as $field) {
            $value = $field->getValue();

            if (!empty($value)) {
                if (is_string($value)) {
                    $sql->ilike($field->getName(), $value);
                } elseif ('created_at' == $field->getName()) {
                    $sql->where('created_at', '>=', new \DateTime('-' . $value . ' seconds'));
                } else {
                    $sql->where($field->getName(), $value);
                }
            }
        }

        return $sql->orderBy('_id', 'DESC')->paginate();
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
