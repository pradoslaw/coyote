<?php
namespace Tests\Integration\BaseFixture\Browser;

use PHPUnit\Framework\TestCase;

class BrowserTest extends TestCase
{
    /**
     * @test
     */
    public function test()
    {
        $browser = $this->browserWithTitle('Preview');
        $this->assertSame('Preview', $browser->getHtmlTitle());
    }

    /**
     * @test
     */
    public function javaScript()
    {
        $browser = $this->browserWithTitle('Before');
        $browser->execute('window.document.title = "After";');
        $this->assertSame('After', $browser->getHtmlTitle());
    }

    /**
     * @test
     */
    public function javaScriptReturn()
    {
        $browser = new Browser();
        $result = $browser->execute('return "Foo";');
        $this->assertSame('Foo', $result);
    }

    private function browserWithTitle(string $title): Browser
    {
        $browser = new Browser();
        $browser->setHtmlSource("<html><head><title>$title</title></head></html>");
        return $browser;
    }
}
