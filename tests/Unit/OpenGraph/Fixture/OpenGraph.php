<?php
namespace Tests\Unit\OpenGraph\Fixture;

use Tests\Unit\BaseFixture\Server;

trait OpenGraph
{
    use Server\Http;

    function ogProperty(string $property, string $uri): string
    {
        $view = new ViewFixture($this->htmlView($uri));
        return $view->metaProperty($property);
    }

    function htmlView(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
    }
}
