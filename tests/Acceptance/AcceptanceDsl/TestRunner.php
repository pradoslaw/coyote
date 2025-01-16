<?php
namespace Tests\Acceptance\AcceptanceDsl;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;

readonly class TestRunner
{
    public WebDriver $webDriver;
    private RemoteWebDriver $seleniumDriver;

    public function __construct(private string $screenshotPath)
    {
        $this->seleniumDriver = new Selenium()->createDriver();
        $this->webDriver = new WebDriver($this->seleniumDriver, $screenshotPath);
    }

    public function finishSuite(): void
    {
        $this->seleniumDriver->quit();
    }

    public function resetState(): void
    {
        $this->webDriver->clearCookies();
        $this->webDriver->clearLocalStorage();
    }

    public function captureScreenshotForTest(TestCase $testCase): void
    {
        $this->webDriver->screenshot($this->screenshotFilename($testCase));
    }

    private function screenshotFilename(TestCase $testCase): string
    {
        $testFilename = $this->testClassName($testCase) . '.test.' . $testCase->name();
        if (!$testCase->status()->isSuccess()) {
            return "$testFilename.FAIL";
        }
        return $testFilename;
    }

    private function testClassName(TestCase $testCase): string
    {
        return \array_last(\explode('\\', get_class($testCase)));
    }

    public function clearScreenshots(): void
    {
        $this->clear(\rTrim($this->screenshotPath, '/') . '/*.png');
    }

    private static function clear(string $pathMask): void
    {
        foreach (\glob($pathMask) as $file) {
            \unlink($file);
        }
    }
}
