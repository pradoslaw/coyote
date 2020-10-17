<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Topic;

/**
 * @method mixed search(\Coyote\Services\Elasticsearch\QueryBuilderInterface $queryBuilder)
 * @method void setResponse(string $response)
 * @method $this withTrashed()
 */
interface PostRepositoryInterface extends RepositoryInterface
{
    /**
     * Take X posts from topic.
     *
     * @param Topic $topic
     * @param int $page
     * @param int $perPage
     * @return mixed
     */
    public function lengthAwarePagination(Topic $topic, int $page = 0, int $perPage = 10);

    /**
     * Return page number based on ID of post
     *
     * @param $postId
     * @param $topicId
     * @param int $perPage
     * @return mixed
     */
    public function getPage($postId, $topicId, $perPage = 10);

    /**
     * @param $topicId
     * @param $markTime
     * @return mixed
     */
    public function getFirstUnreadPostId($topicId, $markTime);

    /**
     * @param int $userId
     * @param \Coyote\Post $post
     * @return \Coyote\Post
     */
    public function merge($userId, $post);

    /**
     * @param int $postId
     * @return mixed
     */
    public function history(int $postId);

    /**
     * @param int $userId
     * @return mixed
     */
    public function takeRatesForUser($userId);

    /**
     * @param int $userId
     * @return mixed
     */
    public function takeAcceptsForUser($userId);

    /**
     * @param int $userId
     * @return mixed
     */
    public function takeStatsForUser($userId);

    /**
     * @param int $userId
     * @return array
     */
    public function pieChart($userId);

    /**
     * @param int $userId
     * @return array
     */
    public function lineChart($userId);

    /**
     * @param int $userId
     * @return int
     */
    public function countComments($userId);

    /**
     * @param int $userId
     * @return int
     */
    public function countReceivedVotes($userId);

    /**
     * @param int $userId
     * @return int mixed
     */
    public function countGivenVotes($userId);
}
