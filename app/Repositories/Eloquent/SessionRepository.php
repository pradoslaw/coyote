<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\SessionRepositoryInterface;

class SessionRepository extends Repository implements SessionRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Session';
    }

    /**
     * Pobiera liste sesji uzytkownikow ktorzy odwiedzaja dana strone
     *
     * @param null $path
     * @return mixed
     */
    public function viewers($path = null)
    {
        return $this
            ->model
            ->select(['user_id', 'url', 'sessions.id', 'robot', 'users.name AS name', 'groups.name AS group'])
            ->leftJoin('users', 'users.id', '=', $this->raw('user_id'))
            ->leftJoin('groups', 'groups.id', '=', $this->raw('group_id'))
            ->when($path, function ($builder) use ($path) {
                return $builder->where('url', 'LIKE', '%' . $path . '%');
            })
            ->get();
    }

    /**
     * Sprawdza czy dany user jest online. Wykorzystywane np. na stronie profilu uzytkownika Zwracana
     * jest data ostatniej aktywnosci uzytkownika (jezeli ten jest aktualnie online)
     *
     * @param $userId
     * @return \Carbon\Carbon
     */
    public function userLastActivity($userId)
    {
        return $this->model
                ->select('updated_at')
                ->where('user_id', $userId)
                ->orderBy('updated_at', 'DESC')
                ->value('updated_at');
    }
}
