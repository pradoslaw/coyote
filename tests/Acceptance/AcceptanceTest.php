<?php
namespace Tests\Acceptance;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Acceptance\AcceptanceDsl\Dsl;
use Tests\Acceptance\AcceptanceDsl\TestRunner;

class AcceptanceTest extends TestCase
{
    private static TestRunner $runner;
    private Dsl $dsl;

    #[BeforeClass]
    public static function initializeTestRunner(): void
    {
        self::$runner = new TestRunner('/var/www/tests/Acceptance/');
        self::$runner->clearScreenshots();
    }

    #[Before]
    public function initializeDsl(): void
    {
        $this->dsl = new Dsl(self::$runner->webDriver);
    }

    #[After]
    public function afterTest(): void
    {
        self::$runner->captureScreenshotForTest($this);
        self::$runner->resetState();
    }

    #[AfterClass]
    public static function finishSuite(): void
    {
        self::$runner->finishSuite();
    }

    #[Test]
    public function registration(): void
    {
        $this->dsl->driver->closeGdpr();
        $this->dsl->registerUser();
        $this->assertTrue($this->dsl->driver->hasRegistrationConfirmation());
    }

    #[Test]
    public function login(): void
    {
        $this->dsl->driver->closeGdpr();
        $this->dsl->registerUser('Mark', 'mark@mark');
        self::$runner->webDriver->clearCookies();
        $this->dsl->loginUser('Mark');
        $this->assertSame('mark@mark', $this->dsl->readLoggedUserEmail());
    }
}
