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
     * Sprawdza czy dany user jest online. Wykorzystywane np. na stronie profilu uzytkownika
     *
     * @param $userId
     * @return bool
     */
    public static function isUserOnline($userId)
    {
        return count(self::select(['user_id'])->where('user_id', $userId)->get()) > 0;
    }
}
