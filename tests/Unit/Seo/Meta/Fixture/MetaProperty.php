<?php
namespace Tests\Unit\Seo\Meta\Fixture;

use Tests\Unit\BaseFixture\Server;
use Tests\Unit\BaseFixture\View\HtmlFixture;

trait MetaProperty
{
    use Server\Http;

    function metaProperty(string $property, string $uri): string
    {
        $html = new HtmlFixture($this->htmlView($uri));
        return $this->attributeByName($html, $property);
    }

    function attributeByName(HtmlFixture $html, string $name): mixed
    {
        foreach ($html->metaDeclarations() as $meta) {
            if ($meta['name'] === $name) {
                return $meta['content'];
            }
        }
        throw new \Exception("Failed to recognize in view meta name: $name");
    }

    function htmlView(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
    }
}
