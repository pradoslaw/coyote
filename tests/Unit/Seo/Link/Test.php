<?php
namespace Tests\Unit\Seo\Link;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Seo;

class Test extends TestCase
{
    use Seo\Link\Fixture\Assertion;

    /**
     * @test
     */
    public function markdownLink()
    {
        $this->assertRenderPost(
            '[foo](http://external)',
            '<p><a href="http://external" rel="ugc,nofollow">foo</a></p>');
    }

    /**
     * @test
     */
    public function htmlLink()
    {
        $this->assertRenderPost(
            '<a href="http://external">foo</a>',
            '<p><a href="http://external" rel="ugc,nofollow">foo</a></p>');
    }

    /**
     * @test
     */
    public function override()
    {
        $this->assertRenderPost(
            '<a href="http://external" rel="follow">foo</a>',
            '<p><a href="http://external" rel="ugc,nofollow">foo</a></p>');
    }
}
