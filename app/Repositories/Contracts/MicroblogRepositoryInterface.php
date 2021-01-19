<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Microblog;

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
     * @return Microblog\Tag[]
     */
    public function getTags();

    /**
     * @return int
     */
    public function count();

    /**
     * @param int $userId
     * @return null|int
     */
    public function countForUser($userId);

    /**
     * @param int $perPage
     * @param int $page
     * @return Microblog[]
     */
    public function forPage(int $perPage, int $page);

    /**
     * @param int|null $userId
     * @return \Coyote\Tag[]
     */
    public function popularTags(?int $userId);

    public function recent();
}
