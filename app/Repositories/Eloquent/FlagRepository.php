<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\FlagRepositoryInterface;

class FlagRepository extends Repository implements FlagRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Flag';
    }

    public function findAllByModel(string $model, array $ids)
    {
        $key = strtolower(class_basename($model)) . '_id';

        return $this
            ->model
            ->select(['flags.*', 'users.name AS user_name', 'flag_types.name'])
            ->join('flag_types', 'flag_types.id', '=', 'type_id')
            ->join('users', 'users.id', '=', 'user_id')
            ->addSelect($this->raw("metadata->>'$key' AS metadata_id"))
            ->whereRaw("metadata->>'$key' IN(" . $this->implodeIds($ids) . ")")
            ->get();
    }

    public function deleteByModel(string $model, int $id, int $userId)
    {
        $model = strtolower(class_basename($model));
        $key = "{$model}_id";

        $this->model->whereJsonContains("metadata->$key", $id)->update(['moderator_id' => $userId]);
        $this->model->whereJsonContains("metadata->$key", $id)->delete();
    }

    /**
     * @param integer $value
     * @return string
     */
    private function strVal($value)
    {
        return "'" . $value . "'";
    }

    /**
     * @param array $ids
     * @return string
     */
    private function implodeIds(array $ids)
    {
        return implode(',', array_map([&$this, 'strVal'], $ids));
    }
}
