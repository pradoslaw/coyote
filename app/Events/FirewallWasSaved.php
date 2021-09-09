<?php

namespace Coyote\Events;

use Coyote\Firewall;
use Illuminate\Queue\SerializesModels;

class FirewallWasSaved
{
    use SerializesModels;

    /**
     * @var Firewall
     */
    protected $firewall;

    /**
     * Create a new event instance.
     *
     * @param Firewall $firewall
     */
    public function __construct(Firewall $firewall)
    {
        $this->firewall = $firewall;
    }
}
