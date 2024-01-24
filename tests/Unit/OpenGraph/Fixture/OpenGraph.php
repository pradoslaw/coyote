<?php
namespace Tests\Unit\OpenGraph\Fixture;

use Tests\Unit\BaseFixture\Server;
use Tests\Unit\BaseFixture\View\HtmlFixture;

trait OpenGraph
{
    use Server\Http;

    function metaProperty(string $property, string $uri): string
    {
        $html = new HtmlFixture($this->htmlView($uri));
        return $this->attributeByProperty($html, $property);
    }

    function attributeByProperty(HtmlFixture $html, string $property): mixed
    {
        foreach ($html->metaDeclarations() as $meta) {
            if ($meta['property'] === $property) {
                return $meta['content'];
            }
        }
        throw new \Exception("Failed to recognize in view meta property: $property");
    }

    function htmlView(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
    }
}
