<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Microblog;
use Coyote\Models\Scopes\UserRelationsScope;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Tag;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class MicroblogRepository
 * @package Coyote\Repositories\Eloquent
 */
class MicroblogRepository extends Repository implements MicroblogRepositoryInterface
{
    public function model()
    {
        return 'Coyote\Microblog';
    }

    /**
     * @inheritDoc
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        return $this->applyCriteria(function () use ($perPage) {
            return $this->model->whereNull('parent_id')->with('user')->with('assets')->with('tags')->withCount('comments')->paginate($perPage);
        });
    }

    /**
     * @inheritDoc
     */
    public function forPage(int $perPage, int $page)
    {
        return $this->applyCriteria(function () use ($perPage, $page) {
            return $this->model->whereNull('parent_id')->with('user')->with('assets')->with('tags')->withCount('comments')->limit($perPage)->offset(max(0, $page - 1) * $perPage)->get();
        });
    }

    public function recent()
    {
        return $this->applyCriteria(function () {
            return $this->model->whereNull('parent_id')->withoutGlobalScope(UserRelationsScope::class)->limit(5)->orderBy('id', 'DESC')->get();
        });
    }

    /**
     * Pobierz X ostatnich wpisow z mikrobloga przy czym sortowane sa one wedlug oceny. Metoda
     * ta jest wykorzystywana na stronie glownej serwisu
     *
     * @param int $limit
     * @return mixed
     * @throws
     */
    public function getPopular($limit)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->whereNull('parent_id')
            ->with('user')
            ->withCount('comments')
            ->with('assets')
            ->where(function (Builder $builder) {
                return $builder->where('votes', '>=', 2)->orWhere('is_sponsored', true);
            })
            ->take($limit)
            ->get();

        $this->resetModel();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id)
    {
        return $this->applyCriteria(function () use ($id) {
            return $this->model->with('user')->withCount('comments')->with('assets')->withoutGlobalScope(UserRelationsScope::class)->where('microblogs.id', $id)->firstOrFail();
        });
    }

    /**
     * @inheritDoc
     */
    public function getComments($parentId)
    {
        return $this->applyCriteria(function () use ($parentId) {
            return $this->model->with('user')->where('parent_id', $parentId)->orderBy('id')->get();
        });
    }

    /**
     * @param int[] $parentIds
     * @return MicroblogRepository
     */
    public function getTopComments($parentIds)
    {
        $sub = $this->model->selectRaw('*, row_number() OVER (PARTITION BY parent_id ORDER BY id DESC)')->whereIn('parent_id', $parentIds);

        return $this->applyCriteria(function () use ($parentIds, $sub) {
            return $this
                ->model
                ->with('user')
                ->withTrashed()
                ->withoutGlobalScope(UserRelationsScope::class)
                ->fromRaw('(' . $sub->toSql() . ') AS microblogs', $sub->getBindings())
                ->whereRaw('microblogs.row_number <= 2')
                ->orderBy('microblogs.id')
                ->get();
        });
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->model->whereNull('parent_id')->count();
    }

    /**
     * @param int $userId
     * @return null|int
     */
    public function countForUser($userId)
    {
        return $userId ? $this->model->whereNull('parent_id')->where('user_id', $userId)->count() : null;
    }

    /**
     * @inheritDoc
     */
    public function popularTags(?int $userId)
    {
        $db = $this->app['db'];

        $base = $db->table('microblog_tags')
            ->selectRaw('name, tags.text, COUNT(*)')
            ->leftJoin('tags', 'tags.id', '=', 'microblog_tags.tag_id')
            ->leftJoin('microblogs', 'microblogs.id', '=', 'microblog_tags.microblog_id')
            ->groupBy('tags.id')
            ->groupBy('name')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(5);

        $query = clone $base;

        return $query
            ->orderBy("count", 'DESC')
            ->when($userId, function ($builder) use ($base, $userId) {
                return $builder
                    ->where('microblogs.user_id', $userId)
                    ->selectRaw('1 AS "order"')
                    ->limit(3)
                    ->unionAll($base->selectRaw('0 AS "order"')->whereIn('tags.name', ['news', 'programowanie', 'wydarzenia', 'off-topic', 'autopromocja']))
                    ->orderByRaw('"order" asc');
            })
            ->get()
            ->unique('name')
            ->values()
            ->toArray();
    }

    public function recommendedUsers(?int $userId)
    {
        return $this
            ->app[\Coyote\User::class]
            ->select(['user_id', 'users.name', 'users.id', 'users.photo', 'bio', 'location'])
            ->where('microblogs.created_at', '>', now()->subMonth())
            ->whereNull('microblogs.parent_id')
            ->groupBy('user_id')
            ->groupBy('users.name')
            ->groupBy('users.id')
            ->groupBy('users.photo')
            ->orderByRaw('COUNT(*) DESC')
            ->join('microblogs', 'users.id', '=', 'user_id')
            ->when($userId, function ($builder) use ($userId) {
                return $builder->where('user_id', '!=', $userId)->whereNotIn('user_id', function ($query) use ($userId) {
                    return $query->select('related_user_id')->from('user_relations')->where('user_id', $userId)->where('is_blocked', false);
                });
            })
            ->limit(5)
            ->get();
    }

    /**
     * Pobiera najpopularniejsze tagi w mikroblogach
     *
     * @return mixed
     */
    public function getTags()
    {
        return (new Tag())
                ->select(['name', $this->raw('microblogs AS count'), 'logo', 'category_id'])
                ->orderBy('microblogs', 'DESC')
                ->limit(30)
                ->get();
    }
}
