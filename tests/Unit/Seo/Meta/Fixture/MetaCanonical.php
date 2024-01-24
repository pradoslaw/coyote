<?php
namespace Tests\Unit\Seo\Meta\Fixture;

use PHPUnit\Framework\Assert;
use Tests\Unit\BaseFixture\Server;
use Tests\Unit\BaseFixture\View\ViewDom;

trait MetaCanonical
{
    use Server\Http;
    use Server\RelativeUri;

    function assertSelfCanonical(string $uri): void
    {
        Assert::assertThat(
            $this->metaCanonical($uri),
            $this->relativeUri($uri));
    }

    function metaCanonical(string $uri): string
    {
        $dom = new ViewDom($this->htmlView($uri));
        foreach ($dom->elements(xPath:"/html/head/link[@rel='canonical']") as $canonical) {
            return $canonical->getAttribute('href');
        }
        throw new \AssertionError('Failed finding <link rel="canonical"> tag.');
    }

    function htmlView(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
    }
}
