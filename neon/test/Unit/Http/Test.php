<?php
namespace Neon\Test\Unit\Http;

use Neon\View;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class Test extends TestCase
{
    use BaseFixture\Server\Laravel\Application;
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
        $this->laravel->app->instance(View::class, new View('Ours is the fury'));
        $response = $this->server->get($uri)
            ->assertSuccessful()
            ->getContent();
        $this->assertSame(
            '<!DOCTYPE html><html><title>Ours is the fury</title></html>',
            $response);
    }
}
