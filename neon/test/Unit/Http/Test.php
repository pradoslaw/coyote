<?php
namespace Neon\Test\Unit\Http;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class Test extends TestCase
{
    use BaseFixture\Server\Http;

    /**
     * @test
     */
    public function http(): void
    {
        $this->assertRendersView('/events');
    }

    private function assertRendersView(string $uri): void
    {
        $response = $this->server->get($uri)
            ->assertSuccessful()
            ->getContent();
        $this->assertSame(
            '<!DOCTYPE html><html><title></title></html>',
            $response);
    }
}
