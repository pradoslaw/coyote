<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Job;

/**
 * @method mixed search(\Coyote\Services\Elasticsearch\QueryBuilderInterface $queryBuilder)
 * @method $this withTrashed()
 */
interface JobRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int[] $ids
     * @return Job[]
     */
    public function findManyWithOrder(array $ids);

    /**
     * Get subscribed job offers for given user id
     *
     * @param int $userId
     * @return mixed
     */
    public function subscribes($userId);

    /**
     * @param int $userId
     * @return \Coyote\Feature[]
     */
    public function getDefaultFeatures($userId);

    /**
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function published($userId);

    /**
     * @param array $tags
     * @return array
     */
    public function getTagSuggestions(array $tags): array;
}
