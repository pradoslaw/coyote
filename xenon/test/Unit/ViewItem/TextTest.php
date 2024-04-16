<?php
namespace Xenon\Test\Unit\ViewItem;

use PHPUnit\Framework\TestCase;
use Xenon\Tag;
use Xenon\Test\Unit\Fixture;
use Xenon\Text;
use Xenon\Xenon;

class TextTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function plainText(): void
    {
        $this->xenon = new Xenon([new Tag('div', [new Text('3 <script> 2')])], []);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, '<div>3 &lt;script&gt; 2</div>');
    }

    /**
     * @test
     */
    public function spa(): void
    {
        $this->assertHtmlRuntime($this->xenon, '<div>3 &lt;script&gt; 2</div>');
    }
}
