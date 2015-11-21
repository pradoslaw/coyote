<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Microblog;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Image;

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
     * @param null|int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 10)
    {
        $result = $this->buildQuery()
                ->whereNull('parent_id')
                ->orderBy('microblogs.is_sponsored', 'DESC')
                ->orderBy('microblogs.id', 'DESC')
                ->paginate($perPage);

        // generuje url do miniaturek dolaczonych do wpisu
        $result = $this->thumbnails($result);
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
        $result = $this->buildQuery()
                ->whereNull('parent_id')
                ->where('votes', '>=', 2)
                    ->orWhere('bonus', '>', 0)
                ->orderBy('microblogs.is_sponsored', 'DESC')
                ->orderBy('microblogs.score', 'DESC')
                ->take($limit)
                ->get();

        // generuje url do miniaturek dolaczonych do wpisu
        $result = $this->thumbnails($result);
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
        $result = $this->buildQuery(false)
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
        return $this->buildQuery(false)->whereIn('parent_id', $parentId)->orderBy('id')->get();
    }

    /**
     * Metoda generuje URL do miniaturek jezeli zostaly one dolaczone do wpisu
     *
     * @param mixed $microblogs
     * @return mixed
     */
    public function thumbnails($microblogs)
    {
        $apply = function ($microblog) {
            if (isset($microblog->media['image'])) {
                $thumbnails = [];

                foreach ($microblog->media['image'] as $name) {
                    $thumbnails[$name] = Image::url(url('/storage/microblog/' . $name), 180, 180);
                }

                $microblog->thumbnails = $thumbnails;
            }
        };

        if ($microblogs instanceof Microblog) {
            $apply($microblogs);
        } else {
            $microblogs->each($apply);
        }

        return $microblogs;
    }

    /**
     * @return mixed
     */
    private function buildQuery($withComments = true)
    {
        $columns = ['microblogs.*', 'users.name', 'is_active', 'is_blocked'];
        $columnThumb = 'mv.id AS thumbs_on';
        $columnWatch = 'mw.user_id AS watch_on';

        $userId = auth()->check() ? \DB::raw(auth()->user()->id) : null;

        $query = $this->model;

        if ($withComments) {
            $hasMany = function ($sql) use ($columns, $userId, $columnThumb) {
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

            $query = $query->with(['comments' => $hasMany]);
        }

        if ($userId) {
            $columns = array_merge($columns, [$columnThumb, $columnWatch]);
        }

        $query = $query->select($columns)->join('users', 'users.id', '=', 'user_id');

        if ($userId) {
            $query->leftJoin('microblog_votes AS mv', function ($join) use ($userId) {
                $join->on('mv.microblog_id', '=', 'microblogs.id')
                    ->where('mv.user_id', '=', $userId);
            })
            ->leftJoin('microblog_watch AS mw', function ($join) use ($userId) {
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
     * Pobiera loginy osob ktore oddaly glos na dany wpis
     *
     * @param int $id
     * @return mixed
     */
    public function getVoters($id)
    {
        return (new Microblog\Vote())
                ->where('microblog_id', $id)
                ->join('users', 'users.id', '=', 'user_id')
                ->select(['users.name'])
                ->get()
                ->lists('name')
                ->toArray();
    }

    /**
     * Pobiera najpopularniejsze tagi w mikroblogach
     *
     * @return mixed
     */
    public function getTags()
    {
        return (new Microblog\Tag())
                ->select(['name', \DB::raw('COUNT(*) AS count')])
                ->join('microblogs', 'microblogs.id', '=', 'microblog_id')
                    ->whereNull('deleted_at')
                ->groupBy('name')
                ->orderBy(\DB::raw('COUNT(*)'), 'DESC')
                ->limit(30)
                ->get()
                ->lists('count', 'name')
                ->toArray();
    }
}
