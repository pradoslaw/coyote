<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\SubscribableInterface;
use Coyote\Topic\Track;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;

/**
 * @method $this withTrashed()
 * @method mixed search(\Coyote\Services\Elasticsearch\QueryBuilderInterface $queryBuilder)
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
     * @inheritDoc
     *
     * @uses \Coyote\Topic\user
     */
    public function lengthAwarePagination($userId, string $guestId, $order = 'topics.last_post_id', $direction = 'DESC', $perPage = 20)
    {
        $this->applyCriteria();

        $count = $this->model->count();
        $page = LengthAwarePaginator::resolveCurrentPage();

        $result = $this
            ->model
            ->select([
                'topics.*',
                'pa.post_id AS accepted_id',
                'p.user_id',
                'p.user_name'
            ])
            ->with([
                'user' => function (BelongsTo $builder) {
                    return $builder->select(['id', 'name', 'deleted_at', 'is_blocked', 'photo'])->withTrashed();
                },
                'lastPost' => function ($builder) {
                    return $builder->select(['id', 'topic_id', 'user_id', 'created_at', 'user_name'])->with(['user' => function (BelongsTo $builder) {
                        return $builder->select(['id', 'name', 'deleted_at', 'is_blocked', 'photo'])->withTrashed();
                    }]);
                },
                'forum' => function ($builder) use ($guestId) {
                    return $builder->select(['forums.id', 'name', 'slug'])->withForumMarkTime($guestId);
                }
            ])
            ->withTopicMarkTime($guestId)
            ->leftJoin('post_accepts AS pa', 'pa.topic_id', '=', 'topics.id')
            ->join('posts AS p', 'p.id', '=', 'topics.first_post_id')
            ->with(['tags'])
            ->when($userId, function (Builder $builder) use ($userId) {
                return $builder->addSelect([
                        $this->raw('CASE WHEN ts.created_at IS NULL THEN false ELSE true END AS is_subscribed'),
                        $this->raw('CASE WHEN pv.id IS NULL THEN false ELSE true END AS is_voted'),
                        $this->raw('CASE WHEN tu.user_id IS NULL THEN false ELSE true END AS is_replied')
                    ])
                    ->leftJoin('topic_subscribers AS ts', function (JoinClause $join) use ($userId) {
                        $join->on('ts.topic_id', '=', 'topics.id')->on('ts.user_id', '=', $this->raw($userId));
                    })
                    ->leftJoin('post_votes AS pv', function (JoinClause $join) use ($userId) {
                        $join->on('pv.post_id', '=', 'first_post_id')->on('pv.user_id', '=', $this->raw($userId));
                    })
                    ->leftJoin('topic_users AS tu', function (JoinClause $join) use ($userId) {
                        $join->on('tu.topic_id', '=', 'topics.id')->on('tu.user_id', '=', $this->raw($userId));
                    });
            })
            ->sortable($order, $direction, ['id', 'last', 'replies', 'views', 'score'], ['last' => 'topics.last_post_id'])
            ->limit($perPage)
            ->offset(max(0, $page - 1) * $perPage)
            ->get();

        $this->resetModel();

        return new LengthAwarePaginator(
            $result,
            $count,
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    /**
     * @inheritdoc
     */
    public function countUnread($forumId, $markTime, $guestId)
    {
        return $this
            ->model
            ->where('topics.forum_id', $forumId)
            ->when($markTime, function (Builder $builder) use ($markTime) {
                return $builder->where('last_post_created_at', '>', $markTime);
            })
            ->whereNotIn('topics.id', function ($builder) use ($guestId) {
                return $builder
                    ->select('topic_track.topic_id')
                    ->from('topic_track')
                    ->whereColumn('topic_track.topic_id', 'topics.id')
                    ->where('topic_track.guest_id', $guestId);
            })
            ->exists();
    }

    /**
     * @inheritDoc
     */
    public function flushRead(int $forumId, string $guestId)
    {
        return Track::where('forum_id', $forumId)->where('guest_id', $guestId)->delete();
    }

    /**
     * @param string $sort
     * @return mixed
     */
    private function getSubQuery($sort)
    {
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
                    'first_post_id',
                    'rank'])
                ->orderBy($sort, 'DESC')
                ->whereRaw('is_locked = 0')
                ->limit(3000)
                ->toSql();
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function newest($limit = 7)
    {
        $sub = $this->getSubQuery('id');
        $this->applyCriteria();

        $result = $this
            ->model
            ->select(['topics.*', 'forums.name', 'forums.slug AS forum_slug'])
            ->from($this->raw("($sub) AS topics"))
            ->join('forums', 'forums.id', '=', 'forum_id')
            ->where('forums.is_locked', 0)
            ->limit($limit)
            ->get();

        $this->resetModel();
        return $result;
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function voted($limit = 7)
    {
        $this->applyCriteria();

        $result = $this
            ->model
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

        $this->resetModel();
        return $result;
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function interesting($limit = 7)
    {
        $sub = $this->getSubQuery('last_post_id');
        $this->applyCriteria();

        $result = $this
            ->model
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
            ->where('forums.is_locked', 0)
            ->where('replies', '>', 0)
            ->limit($limit)
            ->orderBy('rank', 'DESC')
            ->get();

        $this->resetModel();

        return $result;
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
