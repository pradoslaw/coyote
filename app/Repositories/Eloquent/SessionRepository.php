<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Models\Str;
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
    public function visitedAt($userId, $sessionId = null)
    {
        $key = $userId !== null ? 'user_id' : 'id';
        $value = $userId !== null ? $userId : $sessionId;

        $session = $this
            ->model
            ->select(['sessions.created_at', 'session_log.updated_at'])
            ->where("sessions.$key", $value)
            ->leftJoin('session_log', "session_log.$key", '=', new Str($value))
            ->first();

        return $session->updated_at ?: $session->created_at;
    }
}
