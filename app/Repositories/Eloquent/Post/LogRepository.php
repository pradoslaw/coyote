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
            'tags' => $tags,
            'comment' => $comment
        ]);
    }

    /**
     * @param $postId
     * @return mixed
     */
    public function takeForPost($postId)
    {
        return $this->model->select([
                'post_log.id',
                'post_log.*',
                'posts.user_name',
                'users.name AS author_name',
                $this->raw('users.deleted_at IS NULL AS is_active'),
                'users.is_blocked',
                'users.is_online'
            ])
            ->where('post_id', $postId)
            ->join('posts', 'posts.id', '=', 'post_id')
            ->leftJoin('users', 'users.id', '=', 'post_log.user_id')
            ->orderBy('post_log.id', 'DESC')
            ->get();
    }
}
