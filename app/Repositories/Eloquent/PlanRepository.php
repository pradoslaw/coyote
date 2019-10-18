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
     * @inheritDoc
     */
    public function findDefault(string $name = null): ?Plan
    {
        return $this
            ->model
            ->when($name, function ($builder) use ($name) {
                return $builder->whereRaw('LOWER(name) = ?', strtolower($name));
            })
            ->when(!$name, function ($builder) {
                return $builder->where('is_default', 1);
            })
            ->first();
    }

    /**
     * @inheritdoc
     */
    public function active()
    {
        return $this->model->where('is_active', 1)->orderBy('price')->get();
    }
}
