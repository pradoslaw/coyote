<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\View;

use Tests\Legacy\IntegrationNew\BaseFixture\Server;

trait HtmlView
{
    use Server\Http;

    function htmlView(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
    }
}
