<?php
namespace Xenon\Test\Fixture\ClientRuntime;

use Facebook\WebDriver\Remote\RemoteWebDriver;

class Logger
{
    private array $logs;

    public function __construct(readonly private RemoteWebDriver $driver)
    {
        $this->logs = [];
    }

    public function logs(): array
    {
        $logs = $this->driver->manage()->getLog('browser');
        \array_push($this->logs, ...$logs);
        return $this->logs;
    }

    public function clear(): void
    {
        $this->driver->manage()->getLog('browser');
        $this->logs = [];
    }
}
