<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Activity;
use Coyote\Repositories\Contracts\ActivityRepositoryInterface;

class ActivityRepository extends Repository implements ActivityRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Activity::class;
    }

    /**
     * @inheritdoc
     */
    public function latest(int $limit)
    {
        return $this->applyCriteria(function () use ($limit) {
            return $this->model->select()->latest()->limit($limit)->get();
        });
    }
}
