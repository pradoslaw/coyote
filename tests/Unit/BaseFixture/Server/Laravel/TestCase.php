<?php
namespace Tests\Unit\BaseFixture\Server\Laravel;

use Illuminate\Config;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Symfony\Component\HttpFoundation;
use Tests\Unit\BaseFixture\Server\Laravel\PhpUnit\TestRun;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use OverridesSymfonyRequest;

    /** @var Application */
    public $app;

    public function __construct(private TestRun $run)
    {
        parent::__construct();
    }

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
        /** @var Application $app */
        $app = require __DIR__ . '/../../../../../bootstrap/app.php';
        $this->run->beforeBoot($app);
        /** @var Kernel $kernel */
        $kernel = $app->make(Kernel::class);
        $app->afterBootstrapping(LoadConfiguration::class, $this->disableSentry(...));
        $kernel->bootstrap();
        return $app;
    }

    private function disableSentry(Application $app): void
    {
        /** @var Repository $config */
        $config = $app[Config\Repository::class];
        $config->set('sentry.dsn', '');
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
