<?php

namespace Coyote\Repositories\Contracts;

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
    public function userLastActivity($userId);
}
