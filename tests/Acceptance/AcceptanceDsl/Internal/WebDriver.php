<?php
namespace Tests\Acceptance\AcceptanceDsl\Internal;

use Tests\Acceptance\AcceptanceDsl\Internal\Selenium\SeleniumDriver;

readonly class WebDriver
{
    private SeleniumDriver $selenium;
    private string $baseUrl;

    public function __construct()
    {
        $this->selenium = new SeleniumDriver();
        $this->baseUrl = 'http://nginx';
    }

    public function close(): void
    {
        $this->selenium->close();
    }

    public function clearCookies(): void
    {
        $this->selenium->clearCookies();
    }

    public function navigate(string $relativeUrl): void
    {
        $this->selenium->navigate($this->baseUrl . '/' . \lTrim($relativeUrl, '/'));
    }

    public function click(string $text): void
    {
        $this->selenium->click($text);
    }

    public function findSemanticItem(string $semanticId): string
    {
        return $this->selenium->findByCss(".$semanticId")->text();
    }

    public function captureScreenshot(string $path, string $testCaseName): void
    {
        $this->selenium->captureScreenshot("$path$testCaseName.png");
    }

    public function visible(string $text): bool
    {
        return \in_array($text, $this->selenium->readLineNodes());
    }

    public function fillByCss(string $cssSelector, string $value): void
    {
        $this->selenium->findByCss($cssSelector)->fill($value);
    }
}
