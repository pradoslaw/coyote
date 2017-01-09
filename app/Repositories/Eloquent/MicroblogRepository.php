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
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->prepare()->findOrFail($id, $columns);

        return $result;
    }

    /**
     * @param integer $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 10)
    {
        $this->applyCriteria();
        $result = $this->prepare()
                ->whereNull('parent_id')
                ->orderBy('microblogs.is_sponsored', 'DESC')
                ->orderBy('microblogs.id', 'DESC')
                ->paginate($perPage);

        // zostawiamy jedynie 2 ostatnie komentarze
        $result = $this->slice($result);

        return $result;
    }

    /**
     * Pobierz X ostatnich wpisow z mikrobloga przy czym sortowane sa one wedlug oceny. Metoda
     * ta jest wykorzystywana na stronie glownej serwisu
     *
     * @param int $limit
     * @return mixed
     */
    public function take($limit)
    {
        $result = $this
            ->prepare()
            ->whereNull('parent_id')
            ->where(function (Builder $builder) {
                return $builder->where('votes', '>=', 2)->orWhere('bonus', '>', 0);
            })
            ->orderBy('microblogs.is_sponsored', 'DESC')
            ->orderBy('microblogs.score', 'DESC')
            ->take($limit)
            ->get();

        // zostawiamy jedynie 2 ostatnie komentarze
        $result = $this->slice($result);

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
        $result = $this->prepare(false)
                ->whereNull('parent_id')
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
        return $this->prepare(false)->whereIn('parent_id', $parentId)->orderBy('id')->get();
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
     * @param bool $withComments
     * @return mixed
     */
    private function prepare($withComments = true)
    {
        $columns = ['microblogs.*', 'users.name', 'is_active', 'is_blocked', 'photo'];
        $columnThumb = 'mv.id AS thumbs_on';
        $columnWatch = 'mw.user_id AS subscribe_on';

        $userId = $this->app['auth']->id() ? $this->raw($this->app['auth']->id()) : null;
        $with = [];

        if ($withComments) {
            $with['comments'] = function ($sql) use ($columns, $userId, $columnThumb) {
                $sql->join('users', 'users.id', '=', 'user_id')->orderBy('id', 'ASC');

                if ($userId) {
                    $sql->leftJoin('microblog_votes AS mv', function ($join) use ($userId) {
                        $join->on('mv.microblog_id', '=', 'microblogs.id')
                            ->where('mv.user_id', '=', $userId);
                    });

                    $columns = array_merge($columns, [$columnThumb]);
                }

                $sql->select($columns);
            };
        }

        if ($userId) {
            $columns = array_merge($columns, [$columnThumb, $columnWatch]);
        }

        $query = $this->model->select($columns)->with($with)->join('users', 'users.id', '=', 'user_id');

        if ($userId) {
            $query->leftJoin('microblog_votes AS mv', function ($join) use ($userId) {
                $join->on('mv.microblog_id', '=', 'microblogs.id')
                    ->where('mv.user_id', '=', $userId);
            })
            ->leftJoin('microblog_subscribers AS mw', function ($join) use ($userId) {
                $join->on('mw.microblog_id', '=', 'microblogs.id')
                    ->where('mw.user_id', '=', $userId);
            });
        }

        return $query;
    }

    /**
     * Zostawia jedynie 2 ostatnie komentarze do wpisu
     *
     * @param $microblogs
     * @return mixed
     */
    private function slice($microblogs)
    {
        foreach ($microblogs as &$microblog) {
            $microblog->comments_count = $microblog->comments->count();
            $microblog->comments = $microblog->comments->slice(-2, 2);
        }

        return $microblogs;
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
