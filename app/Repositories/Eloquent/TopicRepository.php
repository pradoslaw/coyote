<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Topic;
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
class TopicRepository extends Repository implements TopicRepositoryInterface
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
            ->getBuilder($userId, $guestId)
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
     * @inheritDoc
     */
    public function findByIds(array $ids, ?int $userId, string $guestId)
    {
        $this->applyCriteria();

        $ordering = [];

        for ($i = 0; $i < count($ids); $i++) {
            $ordering[] = "($ids[$i],$i)";
        }

        $result = $this
            ->getBuilder($userId, $guestId)
            ->whereIn('topics.id', $ids)
            ->join($this->raw('(VALUES ' . implode(',', $ordering) . ') AS x (id, ordering)'), 'topics.id', '=', 'x.id')
            ->orderBy('x.ordering')
            ->get();

        $this->resetModel();

        return $result;
    }

    private function getBuilder(?int $userId, string $guestId)
    {
        return $this
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
                ->leftJoin('subscriptions AS ts', function (JoinClause $join) use ($userId) {
                    $join->on('ts.resource_id', '=', 'topics.id')->where('ts.resource_type', Topic::class)->where('ts.user_id', $userId);
                })
                ->leftJoin('post_votes AS pv', function (JoinClause $join) use ($userId) {
                    $join->on('pv.post_id', '=', 'first_post_id')->on('pv.user_id', '=', $this->raw($userId));
                })
                ->leftJoin('topic_users AS tu', function (JoinClause $join) use ($userId) {
                    $join->on('tu.topic_id', '=', 'topics.id')->on('tu.user_id', '=', $this->raw($userId));
                });
            });
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
     * @param int $limit
     * @return mixed
     */
    public function newest($limit = 7)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->from('topic_recent AS topics')
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
        $this->applyCriteria();

        $result = $this
            ->model
            ->from('topic_recent AS topics')
            ->where('replies', '>', 0)
            ->limit($limit)
            ->orderBy('rank', 'DESC')
            ->get();

        $this->resetModel();

        return $result;
    }
}
