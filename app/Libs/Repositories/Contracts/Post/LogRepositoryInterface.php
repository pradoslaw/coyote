<?php

namespace Coyote\Repositories\Contracts\Post;

use Coyote\Repositories\Contracts\RepositoryInterface;

interface LogRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $postId
     * @param int $userId
     * @param string $text
     * @param string $subject
     * @param array $tags
     * @param string|null $comment
     */
    public function add($postId, $userId, $text, $subject, array $tags, $comment = null);
}
