<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Microblog;
use Coyote\Repositories\Eloquent\MicroblogRepository;

interface MicroblogRepositoryInterface extends RepositoryInterface
{
    /**
     * Pobierz X ostatnich wpisow z mikrobloga przy czym sortowane sa one wedlug oceny. Metoda
     * ta jest wykorzystywana na stronie glownej serwisu
     *
     * @param int $limit
     * @return mixed
     */
    public function getPopular($limit);

    /**
     * Pobiera $limit najpopularniejszych wpisow z mikrobloga z ostatniego tygodnia
     *
     * @param $limit
     * @return mixed
     */
    public function takePopular($limit);

    /**
     * @param int $id
     * @return Microblog
     */
    public function findById(int $id);

    /**
     * Pobranie komentarzy od danego wpisu w mikroblogu
     *
     * @param int $parentId
     * @return Microblog[]
     */
    public function getComments(int $parentId);

    /**
     * @param int[] $parentIds
     * @return Microblog[]
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

    public function recent();
}
