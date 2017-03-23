<?php

namespace Coyote\Repositories\Contracts;

interface FirewallRepositoryInterface extends RepositoryInterface
{
    /**
     * Purge expired firewall entries
     */
    public function purge();
}
