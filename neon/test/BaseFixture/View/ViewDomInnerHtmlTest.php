<?php
namespace Neon\Test\BaseFixture\View;

use PHPUnit\Framework\TestCase;

class ViewDomInnerHtmlTest extends TestCase
{
    /**
     * @test
     */
    public function test(): void
    {
        $dom = new ViewDom('<html>
            <body>
                <a href="#foo">Bar</a> Cat <b>Foo</b>
            </body>
        </html>');
        $this->assertSame(
            '<a href="#foo">Bar</a> Cat <b>Foo</b>',
            $dom->innerHtml('/html/body'),
        );
    }
}
