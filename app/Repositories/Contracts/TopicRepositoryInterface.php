<?php

namespace Coyote\Repositories\Contracts;

/**
 * @package Coyote\Repositories\Contracts
 * @method $this withTrashed()
 * @method mixed search(\Coyote\Services\Elasticsearch\QueryBuilderInterface $queryBuilder)
 */
interface TopicRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int|null $userId
     * @param string $guestId
     * @param string $order
     * @param string $direction
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($userId, string $guestId, $order = 'topics.last_post_id', $direction = 'DESC', $perPage = 20);

    /**
     * Is there any unread topic in this category?
     *
     * @param int $forumId
     * @param string $markTime
     * @param string $guestId
     * @return mixed
     */
    public function isUnread($forumId, $markTime, $guestId);

    /**
     * @param int $limit
     * @return mixed
     */
    public function newest($limit = 7);

    /**
     * @param int $limit
     * @return mixed
     */
    public function voted($limit = 7);

    /**
     * @param int $limit
     * @return mixed
     */
    public function interesting($limit = 7);

    /**
     * @param int $userId
     * @return mixed
     */
    public function getSubscribed($userId);
}
