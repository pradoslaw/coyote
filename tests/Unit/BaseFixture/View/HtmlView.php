<?php
namespace Tests\Unit\BaseFixture\View;

use Tests\Unit\BaseFixture\Server;

trait HtmlView
{
    use Server\Http;

    function htmlView(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
    }
}
