<?php

namespace Coyote\Repositories\Contracts;

interface SettingRepositoryInterface extends RepositoryInterface
{
    /**
     * @param $name
     * @param $value
     * @param $guestId
     */
    public function setItem($name, $value, $guestId);

    /**
     * @param $name
     * @param $guestId
     * @param null $default
     * @return null|static
     */
    public function getItem($name, $guestId, $default = null);

    /**
     * @param $guestId
     * @return array
     */
    public function getAll($guestId);
}
