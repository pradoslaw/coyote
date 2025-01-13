<?php
namespace Tests\Legacy\Browser;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Tests\Legacy\IntegrationOld\CreatesApplication;

abstract class DuskTestCase extends \Laravel\Dusk\TestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        Browser::$baseUrl = $this->baseUrl();
        Browser::$storeScreenshotsAt = base_path('tests/Legacy/Browser/screenshots');
        Browser::$storeConsoleLogAt = base_path('tests/Legacy/Browser/console');
        Browser::$storeSourceAt = base_path('tests/Legacy/Browser/source');
    }

    protected function driver(): RemoteWebDriver
    {
        $chromeOptions = new ChromeOptions;
        $chromeOptions->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--ignore-ssl-errors',
            '--whitelisted-ips=""',
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        return RemoteWebDriver::create('http://selenium:4444/wd/hub', $capabilities);
    }
}
