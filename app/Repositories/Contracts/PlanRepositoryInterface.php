<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Plan;

interface PlanRepositoryInterface extends RepositoryInterface
{
    /**
     * @return int
     */
    public function getDefaultId(): int;

    /**
     * @return Plan[]
     */
    public function active();
}
