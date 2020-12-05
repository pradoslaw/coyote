<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Repositories\Criteria\Criteria;

/**
 * Interface CriteriaInterface
 */
interface CriteriaInterface
{
    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $criteria);

    /**
     * @return $this
     */
    public function applyCriteria();
}
