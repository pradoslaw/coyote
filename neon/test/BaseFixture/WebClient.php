<?php
namespace Neon\Test\BaseFixture;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

readonly class WebClient
{
    private RemoteWebDriver $driver;

    public function __construct()
    {
        $this->driver = $this->seleniumDriver();
    }

    private function seleniumDriver(): RemoteWebDriver
    {
        $options = new ChromeOptions;
        $options->addArguments([
            '--window-size=2560,1440',
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--ignore-ssl-errors',
            '--whitelisted-ips=""',
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        return RemoteWebDriver::create(
            'http://selenium:4444/wd/hub',
            $capabilities);
    }

    public function navigateTo(string $url): void
    {
        $this->driver->navigate()->to($url);
    }

    public function typeAndSubmit(string $cssSelector, string $text): void
    {
        $element = $this->driver->findElement(WebDriverBy::cssSelector($cssSelector));
        $element->sendKeys($text);
        $element->sendKeys(WebDriverKeys::RETURN_KEY);
    }

    public function computedStyle(string $cssSelector): array
    {
        return $this->driver->executeScript(<<<script
            const query = arguments[0];
            const styles = window.getComputedStyle(document.querySelector(query));
            return Object.fromEntries(Array.from(styles).map(style => [style, styles[style]]));
            script, [$cssSelector]);
    }

    public function currentUrl(): string
    {
        return $this->driver->getCurrentURL();
    }

    public function close(): void
    {
        $this->driver->close();
    }
}
