<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Plan;

interface PlanRepositoryInterface extends RepositoryInterface
{
    /**
     * @return Plan[]
     */
    public function active();

    /**
     * @param string|null $name
     * @return Plan|null
     */
    public function findDefault(string $name = null): ?Plan;
}
