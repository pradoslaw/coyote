<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Str;
use Coyote\Repositories\Contracts\SessionRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

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
     * @inheritdoc
     */
    public function extend($sessionId)
    {
        $this->update(['updated_at' => $this->raw('NOW()')], $sessionId);
    }

    /**
     * Pobiera liste sesji uzytkownikow ktorzy odwiedzaja dana strone
     *
     * @param null $path
     * @return mixed
     */
    public function byPath($path = null)
    {
        return $this
            ->model
            ->select(['user_id', 'url', 'sessions.id', 'robot', 'users.name AS name', 'groups.name AS group'])
            ->leftJoin('users', 'users.id', '=', $this->raw('user_id'))
            ->leftJoin('groups', 'groups.id', '=', $this->raw('group_id'))
            ->when($path, function (Builder $builder) use ($path) {
                return $builder->where('url', 'ILIKE', '%' . $path . '%');
            })
            ->get();
    }

    /**
     * @inheritdoc
     */
    public function updatedAt($userId)
    {
        return $this
            ->model
            ->select('updated_at')
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->value('updated_at');
    }

    /**
     * @inheritdoc
     */
    public function findFirstVisit($userId, $sessionId = null)
    {
        $key = $userId !== null ? 'user_id' : 'id';
        $value = $userId !== null ? $userId : new Str($sessionId);

        $result = $this->app['db']->selectOne(
            "SELECT LEAST(
                (SELECT created_at FROM sessions WHERE $key = $value ORDER BY created_at LIMIT 1), 
                (SELECT created_at FROM session_log WHERE $key = $value LIMIT 1)
              )
            "
        );

        if (empty($result->least)) {
            return Carbon::now()->toDateTimeString();
        }

        return $result->least;
    }
}
