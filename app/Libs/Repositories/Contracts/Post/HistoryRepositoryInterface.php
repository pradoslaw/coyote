<?php

namespace Coyote\Repositories\Contracts\Post;

use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Post;
use Coyote\Topic;

interface HistoryRepositoryInterface extends RepositoryInterface
{
    /**
     * Add initial entries to the post history
     *
     * @param int $userId
     * @param Post $post
     * @param Topic $topic
     */
    public function initial($userId, Post $post, Topic $topic = null);

    /**
     * @param int $typeId
     * @param int $postId
     * @param int $userId
     * @param string $data
     * @param string $guid
     */
    public function add($typeId, $postId, $userId, $data, $guid = null);

    /**
     * @return string
     */
    public function guid();
}
