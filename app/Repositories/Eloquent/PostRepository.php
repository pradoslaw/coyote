<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Coyote\Post\Subscriber;
use Coyote\Post\Attachment;
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
                        author.location,
                        author.posts AS author_posts,
                        author.allow_sig,
                        author.allow_smilies,
                        author.allow_count,
                        author.created_at AS author_created_at,
                        author.visited_at AS author_visited_at,
                        editor.name AS editor_name,
                        editor.name AS editor_is_active,
                        editor.name AS editor_is_blocked,
                        groups.name AS group_name,
                        sessions.updated_at AS session_updated_at,
                        pa.user_id AS accept_on'
                    )
                    ->leftJoin('sessions', 'sessions.user_id', '=', 'posts.user_id')
                    ->leftJoin('users AS author', 'author.id', '=', 'posts.user_id')
                    ->leftJoin('users AS editor', 'editor.id', '=', 'editor_id')
                    ->leftJoin('groups', 'groups.id', '=', 'author.group_id')
                    ->leftJoin('post_accepts AS pa', 'pa.post_id', '=', 'posts.id')
                    ->orderBy('posts.id');

        if ($userId) {
            // pobieramy wartosc "id" a nie "created_at" poniewaz kiedys created_at nie bylo zapisywane
            $sql = $sql->addSelect(['pv.id AS vote_on', 'ps.id AS subscribe_on'])
                        ->leftJoin('post_votes AS pv', function ($join) use ($userId) {
                            $join->on('pv.post_id', '=', 'posts.id')->on('pv.user_id', '=', DB::raw($userId));
                        })
                        ->leftJoin('post_subscribers AS ps', function ($join) use ($userId) {
                            $join->on('ps.post_id', '=', 'posts.id')->on('ps.user_id', '=', DB::raw($userId));
                        });
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
     * Take X posts from topic. IMPORTANT: first post of topic will always be fetched
     *
     * @param int $topicId
     * @param int $postId   First post ID (in thread)
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return mixed
     */
    public function takeForTopic($topicId, $postId, $userId, $page = 0, $perPage = 10)
    {
        $first = $this->takeFirst($postId, $userId);

        $this->applyCriteria();
        $sql = $this->prepare($userId)
                    ->where('posts.topic_id', $topicId)
                    ->where('posts.id', '<>', $postId)
                    ->forPage($page, $perPage)
                    ->get()
                    ->prepend($first);

        $sql->load(['comments' => function ($sub) {
            $sub->select([
                'post_comments.*', 'name', 'is_active', 'is_blocked'
            ])->join('users', 'users.id', '=', 'user_id');
        }]);
        $sql->load('attachments');

        return $sql;
    }

    /**
     * Return page number based on ID of post
     *
     * @param $postId
     * @param $topicId
     * @param int $perPage
     * @return mixed
     */
    public function getPage($postId, $topicId, $perPage = 10)
    {
        $count = $this->model->where('topic_id', $topicId)->where('id', '<', $postId)->count();
        return max(0, floor(($count - 1) / $perPage)) + 1;
    }

    /**
     * @param $topicId
     * @param $markTime
     * @return mixed
     */
    public function getFirstUnreadPostId($topicId, $markTime)
    {
        return $this->model
                    ->select('id')
                    ->where('topic_id', $topicId)
                        ->where('created_at', '>', $markTime)
                    ->pluck('id');
    }

    /**
     * Find posts by given ID. We use this method to retrieve quoted posts
     *
     * @param array $postsId
     * @param int $topicId
     * @return mixed
     */
    public function findPosts(array $postsId, $topicId)
    {
        return $this->model
                ->select(['posts.*', 'users.name'])
                ->leftJoin('users', 'users.id', '=', 'posts.user_id')
                ->whereIn('posts.id', $postsId)
                ->where('topic_id', $topicId) // <-- this condition for extra security
                ->get();
    }
}
