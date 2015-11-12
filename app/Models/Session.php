<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * Pobiera liste sesji uzytkownikow ktorzy odwiedzaja dana strone
     *
     * @param null $path
     * @return mixed
     */
    public function getViewers($path = null)
    {
        $sql = $this->select(['user_id', 'url', 'robot', 'users.name AS name', 'groups.name AS group'])
                    ->leftJoin('users', 'users.id', '=', \DB::raw('user_id'))
                    ->leftJoin('groups', 'groups.id', '=', \DB::raw('group_id'));

        if ($path) {
            $sql->where('url', 'LIKE', '%' . $path . '%');
        }

        return $sql->get();
    }

    /**
     * Sprawdza czy dany user jest online. Wykorzystywane np. na stronie profilu uzytkownika Zwracana
     * jest data ostatniej aktywnosci uzytkownika (jezeli ten jest aktualnie online)
     *
     * @param $userId
     * @return \Carbon\Carbon
     */
    public static function getUserSessionTime($userId)
    {
        return self::select('updated_at')
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->pluck('updated_at');
    }
}
