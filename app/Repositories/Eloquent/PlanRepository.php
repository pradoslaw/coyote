<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Plan;
use Coyote\Repositories\Contracts\PlanRepositoryInterface;

class PlanRepository extends Repository implements PlanRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Plan::class;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultId(): int
    {
        return (int) $this->model->select('id')->where('is_default', 1)->value('id');
    }

    /**
     * @inheritdoc
     */
    public function active()
    {
        return $this->model->where('is_active', 1)->orderBy('price')->get();
    }
}
