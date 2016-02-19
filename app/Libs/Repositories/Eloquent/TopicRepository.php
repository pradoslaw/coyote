<?php

namespace Coyote\Repositories\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Topic\Subscriber;
use Coyote\Topic\Track;
use Coyote\Topic;
use Coyote\Tag;
use DB;

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

        $pagination = $this->model->select(['topics.id'])
                    ->sortable($order, $direction, ['id', 'last', 'replies', 'views', 'score'], ['last' => 'topics.last_post_id'])
                    ->paginate($perPage);

        $values = [];
        $pagination->lists('id')->each(function ($item, $key) use (&$values) {
            $values[] = "($item,$key)";
        });

        $from = \DB::table('topics AS t')
                    ->select(['t.*', 'x.ordering'])
                    ->join(\DB::raw('(VALUES ' . implode(',', $values) . ') AS x (id, ordering)'), 't.id', '=', 'x.id')
                    ->orderBy('x.ordering')
                    ->toSql();

        $sql = $this->makeModel()
                    ->withTrashed()
                    ->select([
                        'topics.*',
                        'forum_track.marked_at AS forum_marked_at',
                        'topic_track.marked_at AS topic_marked_at',
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
                        'prev.name AS prev_forum_name',
                        'pa.post_id AS post_accept_id'
                    ])
                    ->from(\DB::raw("($from) AS topics"))
                    ->join('forums', 'forums.id', '=', 'topics.forum_id')
                    ->leftJoin('forums AS prev', 'prev.id', '=', 'prev_forum_id')
                    ->join('posts AS first', 'first.id', '=', 'topics.first_post_id')
                    ->join('posts AS last', 'last.id', '=', 'topics.last_post_id')
                    ->leftJoin('users AS author', 'author.id', '=', 'first.user_id')
                    ->leftJoin('users AS poster', 'poster.id', '=', 'last.user_id')
                    ->leftJoin('forum_track', function ($join) use ($userId, $sessionId) {
                        $join->on('forum_track.forum_id', '=', 'topics.forum_id');

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
                    ->leftJoin('post_accepts AS pa', 'pa.topic_id', '=', 'topics.id')
                    ->with('tags')
                    ->orderBy('topics.ordering');

        if ($userId) {
            $sql = $sql->addSelect(['ts.created_at AS subscribe_on'])
                        ->leftJoin('topic_subscribers AS ts', function ($join) use ($userId) {
                            $join->on('ts.topic_id', '=', 'topics.id')->on('ts.user_id', '=', DB::raw($userId));
                        });
        }

        $result = $sql->get();

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

        return new LengthAwarePaginator(
            $result, $pagination->total(), $perPage, LengthAwarePaginator::resolveCurrentPage(), [
                'path' => LengthAwarePaginator::resolveCurrentPath()
            ]
        );
    }

    /**
     * @param $topicId
     * @param $userId
     * @param $sessionId
     * @return mixed
     */
    public function markTime($topicId, $userId, $sessionId)
    {
        $sql = Track::select('marked_at')->where('topic_id', $topicId);

        if ($userId) {
            $sql->where('user_id', $userId);
        } else {
            $sql->where('session_id', $sessionId);
        }

        return $sql->pluck('marked_at');
    }

    /**
     * Save topic's tags
     *
     * @param int $topicId
     * @param array $tags
     */
    public function setTags($topicId, array $tags)
    {
        Topic\Tag::where('topic_id', $topicId)->delete();

        foreach ($tags as $name) {
            $tag = Tag::firstOrCreate(['name' => $name]);
            Topic\Tag::create(['topic_id' => $topicId, 'tag_id' => $tag->id]);
        }
    }

    /**
     * Enable/disable subscription for this topic
     *
     * @param int $topicId
     * @param int $userId
     * @param bool $flag
     */
    public function subscribe($topicId, $userId, $flag)
    {
        if (!$flag) {
            Subscriber::where('topic_id', $topicId)->where('user_id', $userId)->delete();
        } else {
            Subscriber::firstOrCreate(['topic_id' => $topicId, 'user_id' => $userId]);
        }
    }

    /**
     * Mark topic as read
     *
     * @param $topicId
     * @param $forumId
     * @param $markTime
     * @param $userId
     * @param $sessionId
     */
    public function markAsRead($topicId, $forumId, $markTime, $userId, $sessionId)
    {
        // builds data to update
        $attributes = ['topic_id' => $topicId] + ($userId ? ['user_id' => $userId] : ['session_id' => $sessionId]);
        // execute a query...
        Track::updateOrCreate($attributes, $attributes + ['marked_at' => $markTime, 'forum_id' => $forumId]);
    }

    /**
     * Is there any unread topic in this category?
     *
     * @param $forumId
     * @param $markTime
     * @param $userId
     * @param $sessionId
     * @return mixed
     */
    public function isUnread($forumId, $markTime, $userId, $sessionId)
    {
        $sql = $this->model
                    ->leftJoin('topic_track', function ($join) use ($userId, $sessionId) {
                        $join->on('topic_track.topic_id', '=', 'topics.id');

                        if ($userId) {
                            $join->on('topic_track.user_id', '=', \DB::raw($userId));
                        } else {
                            $join->on('topic_track.session_id', '=', \DB::raw("'" . $sessionId . "'"));
                        }
                    })
                    ->where('topics.forum_id', $forumId)
                    ->whereNull('topic_track.topic_id');

        if ($markTime) {
            $sql->where('last_post_created_at', '>', $markTime);
        }

        return $sql->count();
    }

    /**
     * Lock/unlock topic
     *
     * @param int $topicId
     * @param bool $flag
     */
    public function lock($topicId, $flag)
    {
        $this->update(['is_locked' => $flag], $topicId);
    }

    /**
     * @param int $topicId
     * @param int $value
     */
    public function addViews($topicId, $value = 1)
    {
        $this->model->timestamps = false;
        $this->model->where('id', $topicId)->update(['views' => \DB::raw('views + ' . $value)]);
        $this->model->timestamps = true;
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function newest($limit = 7)
    {
        $sub = $this->model
                    ->select(['id', 'forum_id', 'subject', 'path', 'last_post_created_at', 'views', 'score', 'deleted_at'])
                    ->orderBy('id', 'DESC')
                    ->limit(3000)
                    ->toSql();

        $this->applyCriteria();

        return $this->model
                    ->select(['topics.*', 'forums.name', 'forums.path AS forum_path'])
                    ->from(\DB::raw("($sub) AS topics"))
                    ->join('forums', 'forums.id', '=', 'forum_id')
                    ->where('forums.is_locked', 0)
                    ->limit($limit)
                    ->get();
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function voted($limit = 7)
    {
        // ze wzgledu na blad w aplikowaniu kryteriow, zapytanie z metody newset() laczone jest
        // z tym z metody voted(). nalezy wiec utworzyc nowy obiekt modelu! do poprawy
        $this->makeModel();
        $this->applyCriteria();

        return $this->model
                    ->select([
                        'topics.id',
                        'forum_id',
                        'subject',
                        'topics.path',
                        'forums.name AS forum_name',
                        'forums.path AS forum_path',
                        'last_post_created_at',
                        'views',
                        'score',
                        'deleted_at'
                    ])
                    ->join('forums', 'forums.id', '=', 'forum_id')
                    ->where('last_post_created_at', '>', date('Y-m-d', strtotime('-1 month')))
                    ->where('forums.is_locked', 0)
                    ->where('topics.is_locked', 0)
                    ->orderBy('score', 'DESC')
                    ->orderBy('views', 'DESC')
                    ->limit($limit)
                    ->get();
    }

    /**
     * @param int $limit
     */
    public function interesting($limit = 7)
    {
        //
    }
}
