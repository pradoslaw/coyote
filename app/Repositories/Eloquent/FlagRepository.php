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

    public function findAllByModel(string $model)
    {
        $key = strtolower(class_basename($model)) . '_id';

        return $this
            ->model
            ->select(['flags.*', 'users.name AS user_name', 'flag_types.name'])
            ->join('flag_types', 'flag_types.id', '=', 'type_id')
            ->join('users', 'users.id', '=', 'user_id')
            ->addSelect($this->raw("metadata->>'$key' AS metadata_id"))
            ->whereRaw("jsonb_exists(metadata::jsonb, '$key')")
            ->get();
    }

    public function deleteByModel(string $model, int $id, int $userId = null)
    {
        $model = strtolower(class_basename($model));
        $key = "{$model}_id";

        $this->model->whereJsonContains("metadata->$key", $id)->update(['moderator_id' => $userId]);
        $this->model->whereJsonContains("metadata->$key", $id)->delete();
    }
}
