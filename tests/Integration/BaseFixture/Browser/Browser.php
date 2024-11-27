<?php
namespace Tests\Integration\BaseFixture\Browser;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class Browser
{
    private RemoteWebDriver $driver;

    public function __construct()
    {
        $this->driver = RemoteWebDriver::create(
            'http://selenium:4444/wd/hub',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                (new ChromeOptions)->addArguments([
                    '--disable-gpu',
                    '--headless',
                    '--no-sandbox',
                    '--ignore-ssl-errors',
                    '--whitelisted-ips=""',
                ])));
    }

    public function setHtmlSource(string $htmlSource): void
    {
        $this->driver->get("data:text/html;charset=utf-8,$htmlSource");
    }

    public function execute(string $javaScript): mixed
    {
        return $this->driver->executeScript($javaScript);
    }

    public function getHtmlTitle(): string
    {
        return $this->driver->getTitle();
    }
}
