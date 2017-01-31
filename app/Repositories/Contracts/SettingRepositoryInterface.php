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
     * @param null $default
     * @return null|static
     */
    public function getItem($name, $userId, $sessionId, $default = null);

    /**
     * @param $userId
     * @param $sessionId
     * @return array
     */
    public function getAll($userId, $sessionId);
}
