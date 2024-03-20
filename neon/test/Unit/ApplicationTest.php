<?php
namespace Neon\Test\Unit;

use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class ApplicationTest extends TestCase
{
    use BaseFixture\Server\Http;

    /**
     * @test
     */
    public function sectionTitle(): void
    {
        $dom = $this->dom('/events');
        $this->assertSame(
            'Incoming events',
            $dom->find('/html/body/h1/text()'));
    }

    private function dom(string $uri): ViewDom
    {
        return new ViewDom($this->server->get($uri)->assertSuccessful()->getContent());
    }
}
