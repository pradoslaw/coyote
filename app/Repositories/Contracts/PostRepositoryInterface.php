<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Topic;
use Coyote\User;
use Illuminate\Http\Request;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Poll;

/**
 * @method \Coyote\Services\Elasticsearch\ResponseInterface search(array $body)
 * @method void setResponse(string $response)
 */
interface PostRepositoryInterface extends RepositoryInterface
{
    /**
     * Take first post in thread
     *
     * @param int $postId
     * @param int $userId
     * @return mixed
     */
    public function takeFirst($postId, $userId);

    /**
     * Take X posts from topic. IMPORTANT: first post of topic will be always fetched
     *
     * @param int $topicId
     * @param int $postId   First post ID (in thread)
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return mixed
     */
    public function takeForTopic($topicId, $postId, $userId, $page = 0, $perPage = 10);

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
     * @param Request $request
     * @param User|null $user
     * @param Forum $forum
     * @param Topic $topic
     * @param Post $post
     * @param Poll|null $poll
     * @return Post $post
     */
    public function save(Request $request, $user, Forum $forum, Topic $topic, Post $post, $poll);
}
