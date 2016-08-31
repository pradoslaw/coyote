<?php

namespace Coyote\Repositories\Contracts;

interface FirewallRepositoryInterface extends RepositoryInterface
{
    /**
     * @param $userId
     * @param $ip
     * @return \Coyote\Firewall|null
     */
    public function filter($userId, $ip);

    /**
     * Purge expired firewall entries
     */
    public function purge();
}
