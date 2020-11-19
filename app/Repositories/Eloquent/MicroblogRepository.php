<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Microblog;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Repositories\Contracts\SubscribableInterface;
use Coyote\Tag;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class MicroblogRepository
 * @package Coyote\Repositories\Eloquent
 */
class MicroblogRepository extends Repository implements MicroblogRepositoryInterface, SubscribableInterface
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
            return $this->model->whereNull('parent_id')->withVoters()->with('user')->withCount('comments')->paginate($perPage);
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
            ->withVoters()
            ->with('user')
            ->withCount('comments')
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
            return $this->model->with('user')->withVoters()->withCount('comments')->where('microblogs.id', $id)->firstOrFail();
        });
    }

    /**
     * @inheritDoc
     */
    public function getComments($parentId)
    {
        return $this->applyCriteria(function () use ($parentId) {
            return $this->model->with('user')->withVoters()->where('parent_id', $parentId)->orderBy('id')->get();
        });
    }

    /**
     * @param int[] $parentIds
     * @return MicroblogRepository
     */
    public function getTopComments($parentIds)
    {
        return $this->applyCriteria(function () use ($parentIds) {
            return $this
                ->model
                ->with('user')
                ->withVoters()
                ->withTrashed()
                ->fromSub(function ($builder) use ($parentIds) {
                    return $builder
                        ->selectRaw('*, row_number() OVER (PARTITION BY parent_id ORDER BY id DESC)')
                        ->from('microblogs')
                        ->whereIn('parent_id', $parentIds)
                        ->whereNull('deleted_at');
                }, 'microblogs')
                ->whereIn('microblogs.row_number', [1, 2])
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

    /**
     * @param int $userId
     * @return mixed
     */
    public function getSubscribed($userId)
    {
        return $this
            ->app
            ->make(Microblog\Subscriber::class)
            ->select(['microblogs.id', 'microblog_subscribers.created_at', 'microblogs.text'])
            ->join('microblogs', 'microblogs.id', '=', 'microblog_id')
            ->where('microblog_subscribers.user_id', $userId)
            ->whereNull('deleted_at')
            ->orderBy('microblog_subscribers.id', 'DESC')
            ->paginate();
    }
}
