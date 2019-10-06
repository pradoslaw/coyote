<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Microblog;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Repositories\Contracts\SubscribableInterface;
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
     * @inheritdoc
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        return $this->applyCriteria(function () use ($perPage) {
            return $this->model->whereNull('parent_id')->paginate($perPage);
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
    public function take($limit)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->whereNull('parent_id')
            ->where(function (Builder $builder) {
                return $builder->where('votes', '>=', 2)->orWhere('bonus', '>', 0);
            })
            ->take($limit)
            ->get();

        $this->resetModel();

        return $result;
    }

    /**
     * Pobiera $limit najpopularniejszych wpisow z mikrobloga z ostatniego tygodnia
     *
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
     * Pobranie komentarzy od danego wpisu w mikroblogu
     *
     * @param array $parentId
     * @return mixed
     */
    public function getComments($parentId)
    {
        return $this->applyCriteria(function () use ($parentId) {
            return $this->model->whereIn('parent_id', $parentId)->orderBy('id')->get();
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
        return (new Microblog\Tag())
                ->select(['name', $this->raw('COUNT(*) AS count')])
                ->join('tags', 'tags.id', '=', 'tag_id')
                ->join('microblogs', 'microblogs.id', '=', 'microblog_id')
                    ->whereNull('microblogs.deleted_at')
                    ->whereNull('microblogs.parent_id')
                ->groupBy('name')
                ->orderBy($this->raw('COUNT(*)'), 'DESC')
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
