<?php
namespace Tests\Unit\OpenGraph\Fixture;

use Tests\Unit\BaseFixture\Server;
use Tests\Unit\BaseFixture\ViewFixture;

trait OpenGraph
{
    use Server\Http;

    function ogProperty(string $property, string $uri): string
    {
        $view = new ViewFixture($this->htmlView($uri));
        return $this->attributeByProperty($view, $property);
    }

    function attributeByProperty(ViewFixture $view, string $property): mixed
    {
        foreach ($view->metaDeclarations() as $element) {
            if ($element['property'] === $property) {
                return $element['content'];
            }
        }
        throw new \Exception("Failed to recognize in view meta property: $property");
    }

    function htmlView(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
    }
}
