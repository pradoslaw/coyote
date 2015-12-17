<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PostRepositoryInterface;
use DB;

class PostRepository extends Repository implements PostRepositoryInterface
{
    /**
     * @return \Coyote\Topic
     */
    public function model()
    {
        return 'Coyote\Post';
    }

    /**
     * @param int $userId
     * @return mixed
     */
    private function prepare($userId)
    {
        $sql = $this->model
                    ->selectRaw(
                        'DISTINCT ON(posts.id)
                        posts.*,
                        author.name AS author_name,
                        author.photo,
                        author.is_active,
                        author.is_blocked,
                        author.sig,
                        author.allow_sig,
                        author.allow_smilies,
                        author.created_at AS author_created_at,
                        author.visited_at AS author_visited_at,
                        editor.name AS editor_name,
                        editor.name AS editor_is_active,
                        editor.name AS editor_is_blocked,
                        groups.name AS group_name,
                        sessions.updated_at AS session_updated_at'
                    )
                    ->leftJoin('sessions', 'sessions.user_id', '=', 'posts.user_id')
                    ->leftJoin('users AS author', 'author.id', '=', 'posts.user_id')
                    ->leftJoin('users AS editor', 'editor.id', '=', 'editor_id')
                    ->leftJoin('groups', 'groups.id', '=', 'author.group_id')
                    ->orderBy('posts.id')
                    ->orderBy('sessions.updated_at', 'DESC');

        if ($userId) {
            $sql = $sql->addSelect(['value'])
                        ->leftJoin('post_votes', 'post_votes.post_id', '=', 'posts.id');
        }

        return $sql;
    }

    /**
     * Take first post in thread
     *
     * @param int $postId
     * @param int $userId
     * @return mixed
     */
    public function takeFirst($postId, $userId)
    {
        return $this->prepare($userId)
                    ->where('posts.id', $postId)
                    ->first();
    }

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
    public function takeForTopic($topicId, $postId, $userId, $page = 0, $perPage = 25)
    {
        $first = $this->takeFirst($postId, $userId);

        return $this->prepare($userId)
                    ->where('topic_id', $topicId)
                    ->where('posts.id', '<>', $postId)
                    ->forPage($page, $perPage)
                    ->get()
                    ->prepend($first);
    }
}
