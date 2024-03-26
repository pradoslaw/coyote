<?php
namespace Neon\Test\Unit;

use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class HttpTest extends TestCase
{
    use BaseFixture\Server\Http;

    /**
     * @test
     */
    public function applicationName(): void
    {
        $dom = $this->dom('/events');
        $this->assertSame(
            '4programmers.net',
            $dom->find('//main//nav/ul/li[1]/text()'));
    }

    private function dom(string $uri): ViewDom
    {
        return new ViewDom($this->server->get($uri)->assertSuccessful()->getContent());
    }
}
