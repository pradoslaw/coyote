<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Microblog;

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
                ->orderBy('microblogs.id', 'DESC')
                ->paginate($perPage);

        // pobranie wartosci rekordow do obliczenia stronnicowania
        $total = $result->total();

        // wyciagamy ID rekordow aby pobrac komentarze do nich
        $parentId = $result->pluck('id');
        // taki kod zwroci tablice zawierajaca w kluczu ID rekordu z tabeli `microblogs`
        $microblogs = $result->keyBy('id')->toArray();

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
                ->where('votes', '>=', 2)
                ->orderBy('microblogs.score', 'DESC')
                ->take($limit)
                ->get();

        // wyciagamy ID rekordow aby pobrac komentarze do nich
        $parentId = $result->pluck('id');
        // taki kod zwroci tablice zawierajaca w kluczu ID rekordu z tabeli `microblogs`
        $microblogs = $result->keyBy('id')->toArray();

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
     * @return mixed
     */
    private function buildQuery()
    {
        $userId = auth()->check() ? \DB::raw(auth()->user()->id) : null;

        return $this->model->select(['microblogs.*', 'users.name', 'is_active', 'is_blocked', 'mv.id AS thumbs_on', 'mw.user_id AS watch_on'])
            ->join('users', 'users.id', '=', 'user_id')
            ->leftJoin('microblog_votes AS mv', function ($join) use ($userId) {
                $join->on('mv.microblog_id', '=', 'microblogs.id')
                    ->where('mv.user_id', '=', $userId);
            })
            ->leftJoin('microblog_watch AS mw', function ($join) use ($userId) {
                $join->on('mw.microblog_id', '=', 'microblogs.id')
                    ->where('mw.user_id', '=', $userId);
            });
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
