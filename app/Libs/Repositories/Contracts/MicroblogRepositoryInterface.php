<?php

namespace Coyote\Repositories\Contracts;

interface MicroblogRepositoryInterface extends RepositoryInterface
{
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
     * Metoda generuje URL do miniaturek jezeli zostaly one dolaczone do wpisu
     *
     * @param mixed $microblogs
     * @return mixed
     */
    public function thumbnails($microblogs);

    /**
     * Pobranie komentarzy od danego wpisu w mikroblogu
     *
     * @param array $parentId
     * @return mixed
     */
    public function getComments($parentId);

    /**
     * Pobiera loginy osob ktore oddaly glos na dany wpis
     *
     * @param int $id
     * @return mixed
     */
    public function getVoters($id);
}
