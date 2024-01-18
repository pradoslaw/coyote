<?php
namespace Tests\Unit\BaseFixture\Server\Laravel;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
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
}
