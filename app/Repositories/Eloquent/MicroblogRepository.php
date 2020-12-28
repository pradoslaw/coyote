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
            return $this->model->whereNull('parent_id')->with('user')->with('assets')->withCount('comments')->paginate($perPage);
        });
    }

    /**
     * @inheritDoc
     */
    public function page(int $perPage, int $page)
    {
        return $this->applyCriteria(function () use ($perPage, $page) {
            return $this->model->whereNull('parent_id')->with('user')->with('assets')->withCount('comments')->limit($perPage)->offset(max(0, $page - 1) * $perPage)->get();
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
     * Pobiera $limit najpopularniejszych wpisow z mikrobloga z ostatniego tygodnia
     *
     * @deprecated
     * @param $limit
     * @return mixed
     */
    public function takePopular($limit)
    {
        $result = $this
            ->model
            ->whereNull('parent_id')
            ->select(['microblogs.*', 'users.name', $this->raw('users.deleted_at IS NULL AS is_active'), 'users.is_blocked', 'users.photo'])
            ->join('users', 'users.id', '=', 'user_id')
            ->where('microblogs.created_at', '>=', Carbon::now()->subWeek())
            ->orderBy('microblogs.score', 'DESC')
            ->take($limit)
            ->get();

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
     * Pobiera najpopularniejsze tagi w mikroblogach
     *
     * @return mixed
     */
    public function getTags()
    {
        return (new Tag())
                ->select(['name', $this->raw('microblogs AS count')])
                ->orderBy('microblogs', 'DESC')
                ->limit(30)
                ->get()
                ->pluck('count', 'name')
                ->toArray();
    }
}
