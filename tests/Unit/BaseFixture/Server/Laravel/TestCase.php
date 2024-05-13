<?php
namespace Tests\Unit\BaseFixture\Server\Laravel;

use Coyote\Domain\Github\GithubStars;
use Illuminate\Config;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Symfony\Component\HttpFoundation;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use OverridesSymfonyRequest;

    /** @var Application */
    public $app;
    /** @var callable */
    private $beforeBoot;

    public function __construct(string $name, callable $beforeBoot)
    {
        parent::__construct($name);
        $this->beforeBoot = $beforeBoot;
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
        ($this->beforeBoot)($app);
        /** @var Kernel $kernel */
        $kernel = $app->make(Kernel::class);
        $this->beforeBoot($app);
        $kernel->bootstrap();
        return $app;
    }

    private function beforeBoot(Application $app): void
    {
        $app->afterBootstrapping(LoadConfiguration::class, $this->disableSentry(...));
        $app->singleton(GithubStars::class, fn() => new Fake\GithubStars());
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
