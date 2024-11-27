<?php
namespace Tests\Integration\BaseFixture\Server;

use Tests\Integration\BaseFixture\Server\Laravel\StaticLaravel;

trait Http
{
    use Laravel\Application;

    var ?Server $server = null;

    /**
     * @before
     */
    function initializeServer(): void
    {
        $this->server = new Server(StaticLaravel::get($this));
    }
}
