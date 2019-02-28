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
     * @param int $limit
     * @return mixed
     */
    public function getPopularTags($limit = 1000);

    /**
     * @param int $userId
     * @return \Coyote\Feature[]
     */
    public function getDefaultFeatures($userId);

    /**
     * Return tags with job offers counter
     *
     * @param array $tagsId
     * @return mixed
     */
    public function getTagsWeight(array $tagsId);

    /**
     * @param int $userId
     * @return mixed
     */
    public function getSubscribed($userId);

    /**
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getPublished($userId);

    /**
     * @param array $tags
     * @return array
     */
    public function getTagSuggestions(array $tags): array;

    /**
     * @param int $userId
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setDraft(int $userId, string $key, string $value): void;

    /**
     * @param int $userId
     * @param string $key
     * @return string|null
     */
    public function getDraft(int $userId, string $key): ?string;

    /**
     * @param int $userId
     */
    public function forgetDraft(int $userId): void;
}
