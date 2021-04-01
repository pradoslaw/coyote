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
            return $this->model->whereNull('parent_id')->with(['user', 'assets', 'tags'])->withCount('comments')->paginate($perPage);
        });
    }

    /**
     * @inheritDoc
     */
    public function forPage(int $perPage, int $page)
    {
        return $this->applyCriteria(function () use ($perPage, $page) {
            return $this->model->whereNull('parent_id')->with(['user', 'assets', 'tags'])->withCount('comments')->limit($perPage)->offset(max(0, $page - 1) * $perPage)->get();
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
    public function popular(int $limit)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->whereNull('parent_id')
            ->with('user')
            ->withCount('comments')
            ->with('assets')
            ->with('tags')
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
            return $this->model->with('user')->withCount('comments')->with(['assets', 'tags'])->withoutGlobalScope(UserRelationsScope::class)->where('microblogs.id', $id)->firstOrFail();
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

        return $db
            ->table('tags')
            ->selectRaw("name, tags.text, (resources->'Coyote\Microblog')::int AS count, 0 as \"order\"")
            ->whereIn('tags.name', ['programowanie', 'wydarzenia', 'off-topic', 'autopromocja'])
            ->when($userId, function ($builder) use ($db, $userId) {
                return $builder
                    ->unionAll(
                        $db
                            ->table('tags')
                            ->selectRaw('name, tags.text, COUNT(*)::int AS count, 1 AS "order"')
                            ->where('microblogs.user_id', $userId)
                            ->leftJoin('tag_resources', 'tags.id', '=', 'tag_resources.tag_id')
                            ->leftJoin('microblogs', 'microblogs.id', '=', 'tag_resources.resource_id')
                            ->where('tag_resources.resource_type', Microblog::class)
                            ->groupBy('tags.id')
                            ->groupBy('tags.name')
                            ->orderByRaw('"count" DESC')
                            ->limit(3)
                    );
            })
            ->orderByRaw('"order" ASC, "count" DESC')
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
            ->orderByRaw('SUM(votes) DESC')
            ->join('microblogs', 'users.id', '=', 'user_id')
            ->when($userId, function ($builder) use ($userId) {
                return $builder->where('user_id', '!=', $userId)->whereNotIn('user_id', function ($query) use ($userId) {
                    return $query->select('related_user_id')->from('user_relations')->where('user_id', $userId);
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
                ->select(['name', 'logo', 'category_id'])
                ->addSelect($this->raw("COALESCE(resources ->> '" . Microblog::class . "', '0')::int AS count"))
                ->orderByRaw("count DESC")
                ->limit(30)
                ->get();
    }
}
