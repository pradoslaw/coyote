<?php
namespace Neon\Test\Unit\View\Fixture;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tests\Unit\BaseFixture;

trait CssFixture
{
    use BaseFixture\Server\Laravel\Application;

    private function computedStyle(string $url, string $querySelector): mixed
    {
        $driver = $this->webDriver();
        $driver->navigate()->to('http://nginx' . $url);
        $result = $driver->executeScript(<<<script
        const query = arguments[0];
        const styles = window.getComputedStyle(document.querySelector(query));
        return Object.fromEntries(Array.from(styles).map(style => [style, styles[style]]));
        script, [$querySelector]);
        $driver->close();
        return $result;
    }

    private function webDriver(): RemoteWebDriver
    {
        $options = new ChromeOptions;
        $options->addArguments([
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
}
