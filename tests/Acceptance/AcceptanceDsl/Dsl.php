<?php
namespace Tests\Acceptance\AcceptanceDsl;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

readonly class Dsl
{
    private Driver $driver;

    public function __construct()
    {
        $this->driver = new Driver();
        $this->driver->clearClientData();
    }

    public function close(): void
    {
        $this->driver->close();
    }

    public function includeDiagnosticArtifact(TestCase $testCase): void
    {
        $this->driver->includeDiagnosticArtifact($testCase->name());
    }
}
