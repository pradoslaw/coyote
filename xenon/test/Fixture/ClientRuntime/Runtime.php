<?php
namespace Xenon\Test\Fixture\ClientRuntime;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\JavascriptErrorException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

readonly class Runtime
{
    private RemoteWebDriver $driver;
    private Logger $logger;

    public function __construct()
    {
        $this->driver = $this->cachedWebDriver();
        $this->logger = new Logger($this->driver);
    }

    private function cachedWebDriver(): mixed
    {
        static $cachedDriver;
        if ($cachedDriver === null) {
            $cachedDriver = $this->createWebDriver();
        }
        return $cachedDriver;
    }

    private function createWebDriver(): RemoteWebDriver
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
        $capabilities->setCapability('goog:loggingPrefs', ['browser' => 'ALL']);
        return RemoteWebDriver::create(
            'http://selenium:4444/wd/hub',
            $capabilities);
    }

    public function setHtmlSource(string $html): void
    {
        $this->loadHtmlSource($html);
        $this->throwScriptException();
    }

    private function loadHtmlSource(string $html): void
    {
        $this->driver->navigate()->to("data:text/html;charset=utf-8," . \rawUrlEncode($html));
    }

    private function throwScriptException(): void
    {
        foreach ($this->logger->logs() as $log) {
            if ($log['level'] === 'INFO') {
                continue;
            }
            throw new ScriptException($this->jsLogErrorMessage($log['message']));
        }
    }

    private function jsLogErrorMessage(string $message): string
    {
        return $this->afterSpace($this->withoutPrefix($message, 'javascript '));
    }

    private function afterSpace(string $string): string
    {
        return \subStr($string, \strPos($string, ' ') + 1);
    }

    public function getDocumentHtml(): string
    {
        return $this->driver->executeScript('return window.document.documentElement.innerHTML;');
    }

    public function consoleLogs(): array
    {
        $logs = [];
        foreach ($this->logger->logs() as $log) {
            $logs[] = $this->consoleLogEntry($log['message']);
        }
        return $logs;
    }

    private function consoleLogEntry(string $entry): string
    {
        $offset = \strPos($entry, ' ', \strLen('console-api') + 1);
        return \subStr($entry, $offset + 1);
    }

    public function executeScript(string $javaScript): void
    {
        try {
            $this->driver->executeScript($javaScript);
        } catch (JavascriptErrorException $exception) {
            throw new ScriptException($this->jsExecutionErrorMessage($exception->getMessage()));
        }
    }

    public function clearConsoleLogs(): void
    {
        $this->logger->clear();
    }

    public function click(string $xPath): void
    {
        $this->driver->findElement(WebDriverBy::xpath($xPath))->click();
    }

    private function jsExecutionErrorMessage(string $message): string
    {
        return $this->withoutPrefix($this->firstLine($message), 'javascript error: ');
    }

    private function firstLine(string $string): string
    {
        return \strStr($string, "\n", true);
    }

    private function withoutPrefix(string $string, string $prefix): string
    {
        return \subStr($string, \strLen($prefix),);
    }

    public function close(): void
    {
        $this->logger->clear();
        $this->loadHtmlSource('');
    }
}
