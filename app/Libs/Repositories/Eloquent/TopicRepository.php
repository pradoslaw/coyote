<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Topic\Track;

class TopicRepository extends Repository implements TopicRepositoryInterface
{
    /**
     * @return \Coyote\Topic
     */
    public function model()
    {
        return 'Coyote\Topic';
    }

    /**
     * @param $userId
     * @param $sessionId
     * @param string $order
     * @param string $direction
     * @param int $perPage
     * @return mixed
     */
    public function paginate($userId, $sessionId, $order = 'topics.last_post_id', $direction = 'DESC', $perPage = 20)
    {
        $this->applyCriteria();

        $sql = $this->model
                    ->select([
                        'topics.*',
                        'forum_track.created_at AS forum_marked_at',
                        'topic_track.created_at AS topic_marked_at',
                        'first.created_at AS first_created_at',
                        'first.user_name AS first_user_name',
                        'last.created_at AS last_created_at',
                        'last.user_name AS last_user_name',
                        'author.id AS author_id',
                        'author.name AS author_name',
                        'author.is_active AS author_is_active',
                        'author.is_blocked AS author_is_blocked',
                        'poster.id AS poster_id',
                        'poster.name AS poster_name',
                        'poster.is_active AS poster_is_active',
                        'poster.is_blocked AS poster_is_blocked',
                        'poster.photo AS poster_photo',
                        'forums.path AS forum_path',
                        'forums.name AS forum_name',
                        'prev.name AS prev_forum_name'
                    ])
                    ->join('forums', 'forums.id', '=', 'topics.forum_id')
                    ->leftJoin('forums AS prev', 'prev.id', '=', 'prev_forum_id')
                    ->leftJoin('posts AS first', 'first.id', '=', 'topics.first_post_id')
                    ->leftJoin('posts AS last', 'last.id', '=', 'topics.last_post_id')
                    ->leftJoin('users AS author', 'author.id', '=', 'first.user_id')
                    ->leftJoin('users AS poster', 'poster.id', '=', 'last.user_id')
                    ->leftJoin('forum_track', function ($join) use ($userId, $sessionId) {
                        $join->on('forum_track.forum_id', '=', 'forums.id');

                        if ($userId) {
                            $join->on('forum_track.user_id', '=', \DB::raw($userId));
                        } else {
                            $join->on('forum_track.session_id', '=', \DB::raw("'" . $sessionId . "'"));
                        }
                    })
                    ->leftJoin('topic_track', function ($join) use ($userId, $sessionId) {
                        $join->on('topic_track.topic_id', '=', 'topics.id');

                        if ($userId) {
                            $join->on('topic_track.user_id', '=', \DB::raw($userId));
                        } else {
                            $join->on('topic_track.session_id', '=', \DB::raw("'" . $sessionId . "'"));
                        }
                    })
                    ->with('tags')
                    ->sortable($order, $direction, ['id', 'last', 'replies', 'views', 'score'], ['last' => 'topics.last_post_id']);

        $result = $sql->paginate($perPage);

        foreach ($result as $topic) {
            $lastMarked = $topic->forum_marked_at ?: (new \DateTime('last month'))->format('Y-m-d H:i:s');
            /*
             * Jezeli data napisania ostatniego posta jest pozniejsza
             * niz data odznaczenia forum jako przeczytanego...
             * ORAZ
             * data napisania ostatniego postu jest pozniejsza niz data
             * ostatniego "czytania" tematu...
             * ODZNACZ JAKO NOWY
             */
            $topic->unread = $topic->last_created_at > $lastMarked && $topic->last_created_at > $topic->topic_marked_at;
        }

        return $result;
    }

    /**
     * @param $topicId
     * @param $userId
     * @param $sessionId
     * @return mixed
     */
    public function markTime($topicId, $userId, $sessionId)
    {
        $sql = Track::select('created_at')->where('topic_id', $topicId);

        if ($userId) {
            $sql->where('user_id', $userId);
        } else {
            $sql->where('session_id', $sessionId);
        }

        return $sql->pluck('created_at');
    }
}
