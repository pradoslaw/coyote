<?php
namespace Tests\Unit\Seo\Meta\Fixture;

use Tests\Unit\BaseFixture\Server;
use Tests\Unit\BaseFixture\View\ViewFixture;

trait MetaProperty
{
    use Server\Http;

    function metaProperty(string $property, string $uri): string
    {
        $view = new ViewFixture($this->htmlView($uri));
        return $this->attributeByName($view, $property);
    }

    function attributeByName(ViewFixture $view, string $name): mixed
    {
        $metaDeclarations = $view->metaDeclarations();
        foreach ($metaDeclarations as $element) {
            if ($element['name'] === $name) {
                return $element['content'];
            }
        }
        throw new \Exception("Failed to recognize in view meta name: $name");
    }

    function htmlView(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
    }
}
