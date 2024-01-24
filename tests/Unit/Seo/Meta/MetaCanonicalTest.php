<?php
namespace Tests\Unit\Seo\Meta;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server;
use Tests\Unit\BaseFixture\View\ViewDom;
use Tests\Unit\Seo;

class MetaCanonicalTest extends TestCase
{
    use Server\Http;
    use Server\RelativeUri;

    /**
     * @test
     */
    public function canonical()
    {
        $this->assertThat(
            $this->metaCanonical('/'),
            $this->relativeUri('/'));
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
