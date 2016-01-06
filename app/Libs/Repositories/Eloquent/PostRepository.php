<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Coyote\Post\Subscriber;
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
                        sessions.updated_at AS session_updated_at'
                    )
                    ->leftJoin('sessions', 'sessions.user_id', '=', 'posts.user_id')
                    ->leftJoin('users AS author', 'author.id', '=', 'posts.user_id')
                    ->leftJoin('users AS editor', 'editor.id', '=', 'editor_id')
                    ->leftJoin('groups', 'groups.id', '=', 'author.group_id')
                    ->orderBy('posts.id')
                    ->orderBy('sessions.updated_at', 'DESC');

        if ($userId) {
            $sql = $sql->addSelect(['pv.created_at AS vote_on', 'ps.created_at AS subscribe_on'])
                        ->leftJoin('post_votes AS pv', 'pv.post_id', '=', 'posts.id')
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
     * Take X posts from topic. IMPORTANT: first post of topic will be always fetched
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
                    ->where('topic_id', $topicId)
                    ->where('posts.id', '<>', $postId)
                    ->forPage($page, $perPage)
                    ->get()
                    ->prepend($first);

        $sql->load(['comments' => function ($sub) {
            $sub->select([
                'post_comments.*', 'name', 'is_active', 'is_blocked'
            ])->join('users', 'users.id', '=', 'user_id');
        }]);
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
     * Enable/disable subscription for this post
     *
     * @param int $postId
     * @param int $userId
     * @param bool $flag
     */
    public function subscribe($postId, $userId, $flag)
    {
        if (!$flag) {
            Subscriber::where('post_id', $postId)->where('user_id', $userId)->delete();
        } else {
            Subscriber::firstOrCreate(['post_id' => $postId, 'user_id' => $userId]);
        }
    }
}
