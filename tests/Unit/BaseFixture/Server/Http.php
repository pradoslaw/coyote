<?php
namespace Tests\Unit\BaseFixture\Server;

trait Http
{
    use Laravel\Application;

    var ?Server $server = null;

    /**
     * @before
     */
    function initializeServer(): void
    {
        $this->server = new Server($this->laravel);
    }
}
