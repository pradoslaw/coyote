<?php

namespace Coyote\Repositories\Contracts;

interface SettingRepositoryInterface extends RepositoryInterface
{
    /**
     * @param $name
     * @param $value
     * @param $userId
     * @param $sessionId
     */
    public function setItem($name, $value, $userId, $sessionId);

    /**
     * @param $name
     * @param $userId
     * @param $sessionId
     * @return null|static
     */
    public function getItem($name, $userId, $sessionId);

    /**
     * @param $userId
     * @param $sessionId
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getAll($userId, $sessionId);
}
