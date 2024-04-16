<?php
namespace Xenon\Test\Unit\ViewItem;

use PHPUnit\Framework\TestCase;
use Xenon\Tag;
use Xenon\Test\Unit\Fixture;
use Xenon\Xenon;

class TagAttributeTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function childTag(): void
    {
        $this->xenon = new Xenon([
            new Tag('div', ['class' => 'container', 'id' => 'foo'], []),
        ], []);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, '<div class="container" id="foo"></div>');
    }

    /**
     * @test
     */
    public function spa(): void
    {
        $this->assertHtmlRuntime($this->xenon, '<div class="container" id="foo"></div>');
    }
}
