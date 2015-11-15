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
        return $this->model->select(['microblogs.*', 'users.name', 'is_active', 'is_blocked', 'mv.id AS thumbs_on'])
            ->join('users', 'users.id', '=', 'user_id')
            ->leftJoin('microblog_votes AS mv', function ($join) {
                $join->on('mv.microblog_id', '=', 'microblogs.id')
                    ->where('mv.user_id', '=', \DB::raw(auth()->user()->id));
            });
    }
}
