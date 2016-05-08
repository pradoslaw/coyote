<?php

namespace Coyote\Repositories\Contracts;

/**
 * @method \Coyote\Services\Elasticsearch\ResponseInterface search(array $body)
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
}
