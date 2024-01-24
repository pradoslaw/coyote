<?php
namespace Tests\Unit\Seo\Meta\Fixture;

use Tests\Unit\BaseFixture\Server;

trait MetaProperty
{
    use Server\Http;

    function metaProperty(string $property, string $uri): string
    {
        $view = new ViewFixture($this->htmlView($uri));
        return $view->metaProperty($property);
    }

    function htmlView(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
    }
}
