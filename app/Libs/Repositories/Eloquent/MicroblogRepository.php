<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Microblog;
use Image;

/**
 * Class MicroblogRepository
 * @package Coyote\Repositories\Eloquent
 */
class MicroblogRepository extends Repository
{
    public function model()
    {
        return 'Coyote\Microblog';
    }

    /**
     * @param null $perPage
     * @param string $pageName
     * @param null $page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $pageName = 'page', $page = null)
    {
        $result = $this->buildQuery()
                ->whereNull('parent_id')
                ->orderBy('microblogs.is_sponsored', 'DESC')
                ->orderBy('microblogs.id', 'DESC')
                ->paginate($perPage);

        // pobranie wartosci rekordow do obliczenia stronnicowania
        $total = $result->total();

        // wyciagamy ID rekordow aby pobrac komentarze do nich
        $parentId = $result->pluck('id');
        // taki kod zwroci tablice zawierajaca w kluczu ID rekordu z tabeli `microblogs`
        $microblogs = $result->keyBy('id')->toArray();
        // generuje url do miniaturek dolaczonych do wpisu
        $microblogs = $this->thumbnails($microblogs);

        $comments = $this->getComments($parentId);
        $microblogs = $this->merge($microblogs, $comments);

        return new \Illuminate\Pagination\LengthAwarePaginator($microblogs, $total, $perPage, $page, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
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
                ->where('votes', '>=', 2)->orWhere('bonus', '>', 0)
                ->orderBy('microblogs.is_sponsored', 'DESC')
                ->orderBy('microblogs.score', 'DESC')
                ->take($limit)
                ->get();

        // wyciagamy ID rekordow aby pobrac komentarze do nich
        $parentId = $result->pluck('id');
        // taki kod zwroci tablice zawierajaca w kluczu ID rekordu z tabeli `microblogs`
        $microblogs = $result->keyBy('id')->toArray();
        // generuje url do miniaturek dolaczonych do wpisu
        $microblogs = $this->thumbnails($microblogs);

        $comments = $this->getComments($parentId);
        return $this->merge($microblogs, $comments);
    }

    /**
     * Pobranie komentarzy od danego wpisu w mikroblogu
     *
     * @param int $parentId
     * @return mixed
     */
    private function getComments($parentId)
    {
        return $this->buildQuery()->whereIn('parent_id', $parentId)->get();
    }

    /**
     * Metoda laczy ze soba dwie tablice: jedna zawierajaca wpisy mikrobloga a druga - komentarze do niej
     *
     * @param array $microblogs
     * @param array $comments
     * @return mixed
     */
    private function merge($microblogs, $comments)
    {
        foreach ($comments as $comment) {
            if (!isset($microblogs[$comment['parent_id']]['comments'])) {
                $microblogs[$comment['parent_id']]['comments'] = [];
            }

            array_push($microblogs[$comment['parent_id']]['comments'], $comment);
        }

        return $microblogs;
    }

    /**
     * Metoda generuje URL do miniaturek jezeli zostaly one dolaczone do wpisu
     *
     * @param $microblogs
     * @return mixed
     */
    private function thumbnails($microblogs)
    {
        foreach ($microblogs as &$microblog) {
            if (isset($microblog['media']['image'])) {
                $microblog['thumbnails'] = [];

                foreach ($microblog['media']['image'] as $name) {
                    $microblog['thumbnails'][$name] = Image::url(url('/storage/microblog/' . $name), 180, 180);
                }
            }
        }

        return $microblogs;
    }

    /**
     * @return mixed
     */
    private function buildQuery()
    {
        $columns = ['microblogs.*', 'users.name', 'is_active', 'is_blocked'];
        $userId = auth()->check() ? \DB::raw(auth()->user()->id) : null;

        if ($userId) {
            $columns = array_merge($columns, ['mv.id AS thumbs_on', 'mw.user_id AS watch_on']);
        }
        $sql = $this->model->select($columns)->join('users', 'users.id', '=', 'user_id');

        if ($userId) {
            $sql->leftJoin('microblog_votes AS mv', function ($join) use ($userId) {
                $join->on('mv.microblog_id', '=', 'microblogs.id')
                    ->where('mv.user_id', '=', $userId);
            })
            ->leftJoin('microblog_watch AS mw', function ($join) use ($userId) {
                $join->on('mw.microblog_id', '=', 'microblogs.id')
                    ->where('mw.user_id', '=', $userId);
            });
        }

        return $sql;
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
}
