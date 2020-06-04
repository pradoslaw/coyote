<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Http\Forms\Forum\PostForm;
use Coyote\Topic;
use Coyote\User;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Poll;

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
     * Find posts by given ID. We use this method to retrieve quoted posts
     *
     * @param array $postsId
     * @param int $topicId
     * @return mixed
     */
    public function findPosts(array $postsId, $topicId);

    /**
     * @param PostForm $form
     * @param User|null $user
     * @param Forum $forum
     * @param Topic $topic
     * @param Post $post
     * @param Poll|null $poll
     * @return Post $post
     */
    public function save(PostForm $form, $user, Forum $forum, Topic $topic, Post $post, $poll);

    /**
     * @param int $userId
     * @param \Coyote\Post $post
     * @return \Coyote\Post
     */
    public function merge($userId, $post);

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
