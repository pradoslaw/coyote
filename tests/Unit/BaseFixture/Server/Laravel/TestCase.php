<?php
namespace Tests\Unit\BaseFixture\Server\Laravel;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Symfony\Component\HttpFoundation;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use OverridesSymfonyRequest;

    /** @var Application */
    public $app;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../../../../../bootstrap/app.php';
        /** @var Kernel $kernel */
        $kernel = $app->make(Kernel::class);
        $kernel->bootstrap();
        return $app;
    }

    protected function prepareUrlForRequest($uri): string
    {
        return $uri;
    }

    protected function mapSymfonyRequest(string $uri, HttpFoundation\Request $request): HttpFoundation\Request
    {
        if (\str_ends_with($uri, '?')) {
            $request->server->set('REQUEST_URI', $request->server->get('REQUEST_URI') . '?');
        }
        return $request;
    }
}
