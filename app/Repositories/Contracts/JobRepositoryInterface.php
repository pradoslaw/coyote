<?php

namespace Coyote\Repositories\Contracts;

/**
 * @method mixed search(\Coyote\Services\Elasticsearch\QueryBuilderInterface $queryBuilder)
 * @method $this withTrashed()
 */
interface JobRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return mixed
     */
    public function findById($id);

    /**
     * @return int
     */
    public function count();

    /**
     * @param string $city
     * @return int
     */
    public function countOffersInCity(string $city);

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
    public function getMyOffers($userId);
}
