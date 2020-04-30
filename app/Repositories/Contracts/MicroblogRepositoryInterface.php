<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Repositories\Eloquent\MicroblogRepository;

interface MicroblogRepositoryInterface extends RepositoryInterface
{
    /**
     * @param integer $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 10);

    /**
     * Pobierz X ostatnich wpisow z mikrobloga przy czym sortowane sa one wedlug oceny. Metoda
     * ta jest wykorzystywana na stronie glownej serwisu
     *
     * @param int $limit
     * @return mixed
     */
    public function take($limit);

    /**
     * Pobiera $limit najpopularniejszych wpisow z mikrobloga z ostatniego tygodnia
     *
     * @param $limit
     * @return mixed
     */
    public function takePopular($limit);

    /**
     * Pobranie komentarzy od danego wpisu w mikroblogu
     *
     * @param array $parentId
     * @return mixed
     * @deprecated
     */
    public function getComments($parentId);

    /**
     * @param int[] $parentIds
     * @return MicroblogRepository
     */
    public function getTopComments($parentIds);

    /**
     * Pobiera najpopularniejsze tagi w mikroblogach
     *
     * @return mixed
     */
    public function getTags();

    /**
     * @param int $userId
     * @return mixed
     */
    public function getSubscribed($userId);

    /**
     * @return int
     */
    public function count();

    /**
     * @param int $userId
     * @return null|int
     */
    public function countForUser($userId);
}
