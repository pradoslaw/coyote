<?php

namespace Coyote\Repositories\Contracts;

use Carbon\Carbon;

interface SessionRepositoryInterface extends RepositoryInterface
{
    /**
     * Pobiera liste sesji uzytkownikow ktorzy odwiedzaja dana strone
     *
     * @param null $path
     * @return mixed
     */
    public function viewers($path = null);

    /**
     * Sprawdza czy dany user jest online. Wykorzystywane np. na stronie profilu uzytkownika Zwracana
     * jest data ostatniej aktywnosci uzytkownika (jezeli ten jest aktualnie online)
     *
     * @param $userId
     * @return \Carbon\Carbon
     */
    public function updatedAt($userId);

    /**
     * Find first user's visit ever. This method is helpful to show unreaded posts/categories since last visit.
     *
     * @param int $userId
     * @param null|string $sessionId
     * @return string|Carbon
     */
    public function findFirstVisit($userId, $sessionId = null);
}
