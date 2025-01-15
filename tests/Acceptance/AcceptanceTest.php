<?php
namespace Tests\Acceptance;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Acceptance\AcceptanceDsl\Dsl;
use Tests\Acceptance\AcceptanceDsl\Selenium;
use Tests\Acceptance\AcceptanceDsl\WebDriver;

class AcceptanceTest extends TestCase
{
    private static ?\Facebook\WebDriver\WebDriver $seleniumDriver = null;
    private Dsl $dsl;
    private WebDriver $webDriver;

    #[BeforeClass]
    public static function clearScreenshots(): void
    {
        self::clear('/var/www/tests/Acceptance/*.png');
    }

    private static function clear(string $pathMask): void
    {
        foreach (\glob($pathMask) as $file) {
            \unlink($file);
        }
    }

    #[Before]
    public function initialize(): void
    {
        if (self::$seleniumDriver === null) {
            self::$seleniumDriver = new Selenium()->createDriver();
        }
        $this->webDriver = new WebDriver(self::$seleniumDriver, '/var/www/tests/Acceptance/');
        $this->dsl = new Dsl($this->webDriver);
    }

    #[After]
    public function afterTest(): void
    {
        if (!$this->status()->isSuccess()) {
            $this->webDriver->screenshot($this->classBaseName() . '.test.' . $this->name());
        } else {
            $this->webDriver->screenshot($this->classBaseName() . '.test.' . $this->name() . '.FAIL');
        }
        $this->webDriver->clearCookies();
        $this->webDriver->clearLocalStorage();
    }

    private function classBaseName(): string
    {
        return \array_last(\explode('\\', get_class($this)));
    }

    #[AfterClass]
    public static function finishSuite(): void
    {
        self::$seleniumDriver->quit();
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
        $this->webDriver->clearCookies();
        $this->dsl->loginUser('Mark');
        $this->assertSame('mark@mark', $this->dsl->readLoggedUserEmail());
    }
}
