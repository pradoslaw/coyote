<?php
namespace Neon\Test\Unit\View;

use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View;
use Neon\View\Head\Favicon;
use PHPUnit\Framework\TestCase;

class FaviconTest extends TestCase
{
    /**
     * @test
     */
    public function test(): void
    {
        $view = new View([new Favicon('https://host/favicon.png')],
            []);

        $this->assertSame(
            '<link rel="shortcut icon" href="https://host/favicon.png" type="image/png">',
            $this->favicon($view));
    }

    private function favicon(View $view): string
    {
        $dom = new ViewDom($view->html());
        return $dom->html('/html/head/link[@rel="shortcut icon"]');
    }
}
