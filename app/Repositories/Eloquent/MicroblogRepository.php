<?php
namespace Coyote\Repositories\Eloquent;

use Coyote\Microblog;
use Coyote\Models\Scopes\UserRelationsScope;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Tag;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Builder;

class MicroblogRepository extends Repository implements MicroblogRepositoryInterface
{
    public function model(): string
    {
        return Microblog::class;
    }

    /**
     * @inheritDoc
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        return $this->applyCriteria(function () use ($perPage) {
            return $this->model
                ->newQuery()
                ->whereNull('parent_id')
                ->with(['user', 'assets', 'tags'])
                ->withCount('comments')
                ->paginate($perPage);
        });
    }

    /**
     * @inheritDoc
     */
    public function forPage(int $pageSize, int $pageNumber)
    {
        return $this->applyCriteria(fn() => $this->model
            ->newQuery()
            ->whereNull('parent_id')
            ->with(['user', 'assets', 'tags'])
            ->withCount('comments')
            ->limit($pageSize)
            ->offset(\max(0, $pageNumber - 1) * $pageSize)
            ->get());
    }

    public function recent()
    {
        return $this->applyCriteria(fn() => $this->model
            ->newQuery()
            ->whereNull('parent_id')
            ->withoutGlobalScope(UserRelationsScope::class)
            ->limit(5)
            ->orderBy('id', 'DESC')
            ->get());
    }

    public function popular(int $pageSize, int $pageNumber): Eloquent\Collection
    {
        return $this->applyCriteria(fn() => $this->model
            ->newQuery()
            ->whereNull('parent_id')
            ->with(['user', 'assets', 'tags'])
            ->withCount('comments')
            ->where(fn(Builder $query) => $query
                ->where('votes', '>=', 2)
                ->orWhere('is_sponsored', true))
            ->limit($pageSize)
            ->offset(\max(0, $pageNumber - 1) * $pageSize)
            ->get());
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id)
    {
        return $this->applyCriteria(function () use ($id) {
            return $this->model
                ->newQuery()
                ->withCount('comments')
                ->with(['user', 'assets', 'tags'])
                ->withoutGlobalScope(UserRelationsScope::class)
                ->where('microblogs.id', $id)
                ->firstOrFail();
        });
    }

    /**
     * @inheritDoc
     */
    public function getComments($parentId)
    {
        return $this->applyCriteria(function () use ($parentId) {
            return $this->model
                ->with(['user', 'assets'])
                ->where('parent_id', $parentId)
                ->orderBy('id')
                ->get();
        });
    }

    public function getTopComments(array $parentIds): Eloquent\Collection
    {
        $sub = $this->model->selectRaw('*, row_number() OVER (PARTITION BY parent_id ORDER BY id DESC)')->whereIn('parent_id', $parentIds);

        return $this->applyCriteria(function () use ($parentIds, $sub) {
            return $this
                ->model
                ->with(['user', 'assets'])
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
        if ($userId) {
            return $this->model
                ->newQuery()
                ->whereNull('parent_id')
                ->where('user_id', $userId)
                ->count();
        }
        return null;
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

    public function getTags()
    {
        return (new Tag())
            ->newQuery()
            ->select(['name', 'logo', 'category_id'])
            ->addSelect($this->raw("COALESCE(resources ->> '" . Microblog::class . "', '0')::int AS count"))
            ->orderByRaw("count DESC")
            ->limit(30)
            ->get();
    }
}
