<?php

namespace Coyote\Repositories\Contracts;

/**
 * @method mixed search(\Coyote\Services\Elasticsearch\QueryBuilderInterface $queryBuilder)
 */
interface StreamRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $userId
     * @param string $ip
     * @param string $browser
     * @return bool
     */
    public function hasLoggedBefore($userId, $ip, $browser);
}
