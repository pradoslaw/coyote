<?php
namespace Coyote\Repositories\Contracts;

use Coyote\Microblog;
use Illuminate\Database\Eloquent;

interface MicroblogRepositoryInterface extends RepositoryInterface
{
    public function popular(int $pageSize, int $pageNumber): Eloquent\Collection;

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

    public function getTopComments(array $parentIds): Eloquent\Collection;

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
     * @param int $pageSize
     * @param int $pageNumber
     * @return Microblog[]
     */
    public function forPage(int $pageSize, int $pageNumber);

    /**
     * @param int|null $userId
     * @return \Coyote\Tag[]
     */
    public function popularTags(?int $userId);

    /**
     * @param int|null $userId
     * @return mixed
     */
    public function recommendedUsers(?int $userId);

    public function recent();
}
