<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\SubscribableInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;

/**
 * @method $this withTrashed()
 */
class TopicRepository extends Repository implements TopicRepositoryInterface, SubscribableInterface
{
    /**
     * @return string
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
     * @return false|LengthAwarePaginator
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

        if (empty($values)) {
            return false;
        }

        $from = \DB::table('topics AS t')
                    ->select(['t.*', 'x.ordering'])
                    ->join($this->raw('(VALUES ' . implode(',', $values) . ') AS x (id, ordering)'), 't.id', '=', 'x.id')
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
                        'forums.slug AS forum_slug',
                        'forums.name AS forum_name',
                        'prev.name AS prev_forum_name',
                        'pa.post_id AS post_accept_id'
                    ])
                    ->from($this->raw("($from) AS topics"))
                    ->join('forums', 'forums.id', '=', 'topics.forum_id')
                    ->leftJoin('forums AS prev', 'prev.id', '=', 'prev_forum_id')
                    ->join('posts AS first', 'first.id', '=', 'topics.first_post_id')
                    ->join('posts AS last', 'last.id', '=', 'topics.last_post_id')
                    ->leftJoin('users AS author', 'author.id', '=', 'first.user_id')
                    ->leftJoin('users AS poster', 'poster.id', '=', 'last.user_id')
                    ->leftJoin('forum_track', function ($join) use ($userId, $sessionId) {
                        $join->on('forum_track.forum_id', '=', 'topics.forum_id');

                        if ($userId) {
                            $join->on('forum_track.user_id', '=', $this->raw($userId));
                        } else {
                            $join->on('forum_track.session_id', '=', $this->raw("'" . $sessionId . "'"));
                        }
                    })
                    ->leftJoin('topic_track', function ($join) use ($userId, $sessionId) {
                        $join->on('topic_track.topic_id', '=', 'topics.id');

                        if ($userId) {
                            $join->on('topic_track.user_id', '=', $this->raw($userId));
                        } else {
                            $join->on('topic_track.session_id', '=', $this->raw("'" . $sessionId . "'"));
                        }
                    })
                    ->leftJoin('post_accepts AS pa', 'pa.topic_id', '=', 'topics.id')
                    ->with('tags')
                    ->orderBy('topics.ordering');

        if ($userId) {
            $sql = $sql->addSelect(['ts.created_at AS subscribe_on'])
                        ->leftJoin('topic_subscribers AS ts', function ($join) use ($userId) {
                            $join->on('ts.topic_id', '=', 'topics.id')->on('ts.user_id', '=', $this->raw($userId));
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
            $result,
            $pagination->total(),
            $perPage,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
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
        return $this
            ->model
            ->leftJoin('topic_track', function ($join) use ($userId, $sessionId) {
                $join->on('topic_track.topic_id', '=', 'topics.id');

                if ($userId) {
                    $join->on('topic_track.user_id', '=', $this->raw($userId));
                } else {
                    $join->on('topic_track.session_id', '=', $this->raw("'" . $sessionId . "'"));
                }
            })
            ->when($markTime, function ($builder) use ($markTime) {
                return $builder->where('last_post_created_at', '>', $markTime);
            })
            ->where('topics.forum_id', $forumId)
            ->whereNull('topic_track.id')
            ->count();
    }

    /**
     * @param string $sort
     * @return mixed
     */
    private function getPackage($sort)
    {
        $this->makeModel();

        return $this->model
                ->select([
                    'id',
                    'forum_id',
                    'subject',
                    'slug',
                    'is_locked',
                    'last_post_created_at',
                    'views',
                    'score',
                    'replies',
                    'deleted_at',
                    'first_post_id'])
                ->orderBy($sort, 'DESC')
                ->limit(3000)
                ->toSql();
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function newest($limit = 7)
    {
        $sub = $this->getPackage('id');
        $this->applyCriteria();

        return $this->model
                    ->select(['topics.*', 'forums.name', 'forums.slug AS forum_slug'])
                    ->from($this->raw("($sub) AS topics"))
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
                        'topics.slug',
                        'forums.name',
                        'forums.slug AS forum_slug',
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
     * @param int $userId
     * @param int $limit
     *
     * @todo sprawdzic na bazie produkcyjnej czy takie zapytanie nie jest zbyt wolne
     */
    public function interesting($userId, $limit = 7)
    {
        $this->makeModel();

        $sub = $this->getPackage('last_post_created_at');
        $this->applyCriteria();

        $algo = 'LEAST(1000, 200 * topics.score) +
                    LEAST(1000, 100 * topics.replies) +
                    LEAST(1000, 15 * topics.views) -
                    (extract(epoch from now()) - extract(epoch from topics.last_post_created_at)) / 4500 -
                (extract(epoch from now()) - extract(epoch from posts.created_at)) / 1000';

        $sql = $this->model
                    ->select([
                        'topics.id',
                        'topics.forum_id',
                        'topics.subject',
                        'topics.slug',
                        'forums.name',
                        'forums.slug AS forum_slug',
                        'last_post_created_at',
                        'views',
                        'topics.score',
                        'topics.deleted_at'
                    ])
                    ->from($this->raw("($sub) AS topics"))
                    ->withTrashed()
                    ->join('forums', 'forums.id', '=', 'forum_id')
                    ->join('posts', 'posts.id', '=', 'first_post_id')
                    ->where('forums.is_locked', 0)
                    ->where('topics.is_locked', 0)
                    ->limit($limit);

        if ($userId) {
            $sql->leftJoin('pages', function ($join) {
                $join
                    ->on('pages.content_id', '=', $this->raw('topics.id'))
                    ->on('pages.content_type', '=', $this->raw('?'));
            })
            ->leftJoin('page_visits AS pv', function ($join) use ($userId) {
                $join->on('pv.page_id', '=', 'pages.id')->on('pv.user_id', '=', $this->raw($userId));
            })
            ->addBinding($this->model(), 'join');

            $algo .= ' - CASE
                            WHEN pv.updated_at IS NOT NULL AND pv.updated_at > last_post_created_at
                            THEN (extract(epoch from pv.updated_at) - extract(epoch from last_post_created_at)) / 450
                            ELSE 0
                        END';
        }

        return $sql->orderBy($this->raw($algo), 'DESC')->get();
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getSubscribed($userId)
    {
        $this->applyCriteria();

        return $this
            ->model
            ->select([
                'subject',
                'topics.slug AS topic_slug',
                'forums.slug AS forum_slug',
                'topics.id',
                'topic_subscribers.created_at'
            ])
            ->join('topic_subscribers', 'topic_id', '=', 'topics.id')
            ->join('forums', 'forums.id', '=', 'forum_id')
            ->where('user_id', $userId)
            ->orderBy('topic_subscribers.id', 'DESC')
            ->paginate();
    }
}
