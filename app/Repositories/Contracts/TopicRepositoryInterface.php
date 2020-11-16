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
    public function lengthAwarePagination($userId, string $guestId, $order = 'topics.last_post_id', $direction = 'DESC', $perPage = 20);

    /**
     * @param array $ids
     * @param int|null $userId
     * @param string $guestId
     * @return \Coyote\Topic[]
     */
    public function findByIds(array $ids, ?int $userId, string $guestId);

    /**
     * Is there any unread topic in this category?
     *
     * @param int $forumId
     * @param string $markTime
     * @param string $guestId
     * @return bool
     */
    public function countUnread($forumId, $markTime, $guestId);

    /**
     * @param int $forumId
     * @param string $guestId
     * @return mixed
     */
    public function flushRead(int $forumId, string $guestId);

    /**
     * @param int $limit
     * @return mixed
     */
    public function newest($limit = 7);

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
