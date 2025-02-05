<?php
namespace Tests\Acceptance\AcceptanceDsl;

use Tests\Acceptance\AcceptanceDsl\Internal\WebDriver;

readonly class Driver
{
    private WebDriver $web;
    private string $diagnosticArtifactPath;

    public function __construct()
    {
        $this->web = new WebDriver();
        $this->diagnosticArtifactPath = '/var/www/tests/Acceptance/';
    }

    public function clearClientData(): void
    {
        $this->web->clearCookies();
    }

    public function close(): void
    {
        $this->web->close();
    }

    public function includeDiagnosticArtifact(string $testCaseName): void
    {
        $this->web->captureScreenshot($this->diagnosticArtifactPath, $testCaseName);
    }

    private function navigateToAdminPanel(): void
    {
        $this->web->navigate('/Login');
        $this->closeGdpr();
        $this->web->fillByCss('input[name="name"]', 'admin');
        $this->web->fillByCss('input[name="password"]', 'admin');
        $this->web->click('Zaloguj się');
        $this->web->navigate('/Adm/Dashboard');
        $this->web->fillByCss('input[name="password"]', 'admin');
        $this->web->click('Logowanie');
    }

    private function closeGdpr(): void
    {
        $this->web->click('Tylko niezbędne');
    }
}
