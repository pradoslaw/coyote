<?php

namespace Coyote\Repositories\Eloquent\Post;

use Coyote\Repositories\Contracts\Post\LogRepositoryInterface;
use Coyote\Repositories\Eloquent\Repository;

class LogRepository extends Repository implements LogRepositoryInterface
{
    /**
     * @return \Coyote\Post\Log
     */
    public function model()
    {
        return 'Coyote\Post\Log';
    }

    /**
     * @param int $postId
     * @param int $userId
     * @param string $text
     * @param string $subject
     * @param array $tags
     * @param string|null $comment
     */
    public function add($postId, $userId, $text, $subject, array $tags, $comment = null)
    {
        $this->model->create([
            'post_id' => $postId,
            'user_id' => $userId,
            'text' => $text,
            'subject' => $subject,
            'tags' => json_encode($tags),
            'comment' => $comment
        ]);
    }
}
