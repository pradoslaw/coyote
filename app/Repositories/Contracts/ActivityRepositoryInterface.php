<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Activity;

/**
 * @method firstOrNew(array $attributes)
 * @method updateOrCreate(array $attributes, array $values = [])
 */
interface ActivityRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $limit
     * @return Activity[]
     */
    public function latest(int $limit);
}
