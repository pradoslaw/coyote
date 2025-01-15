<?php
namespace Tests\Acceptance\AcceptanceDsl;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

readonly class Selenium
{
    public function createDriver(): RemoteWebDriver
    {
        return retry(5, $this->tryCreateDriver(...), 50);
    }

    private function tryCreateDriver(): RemoteWebDriver
    {
        return RemoteWebDriver::create('http://selenium:4444/wd/hub', $this->capabilities());
    }

    private function capabilities(): DesiredCapabilities
    {
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $this->chromeOptions());
        return $capabilities;
    }

    private function chromeOptions(): ChromeOptions
    {
        return new ChromeOptions()->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--ignore-ssl-errors',
            '--whitelisted-ips=""',
        ]);
    }
}
