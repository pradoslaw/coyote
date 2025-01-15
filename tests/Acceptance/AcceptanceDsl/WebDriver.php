<?php
namespace Tests\Acceptance\AcceptanceDsl;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk;
use PHPUnit\Framework\Assert;

readonly class WebDriver
{
    private string $baseUrl;

    public function __construct(
        private RemoteWebDriver $driver,
        private string          $screenshotPath,
    )
    {
        $this->baseUrl = 'http://nginx';
    }

    public function navigate(string $url): void
    {
        $this->driver->navigate()->to($this->baseUrl . $url);
    }

    /**
     * @return string[]
     */
    public function currentTextNodes(): array
    {
        return \explode("\n", $this->find('body')->getText());
    }

    public function screenshot(string $screenshotFilename): void
    {
        $this->fitViewportToContent();
        $this->driver->takeScreenshot($this->formatScreenshotPath($screenshotFilename));
    }

    public function fillInput(string $cssSelector, string $value): void
    {
        $this->find($cssSelector)->sendKeys($value);
    }

    public function selectCheckbox(string $cssSelector): void
    {
        $checkbox = $this->find($cssSelector);
        if (!$checkbox->isSelected()) {
            $checkbox->click();
        } else {
            throw new \RuntimeException('Unexpected checkbox state');
        }
    }

    public function pressButton(string $buttonText): void
    {
        $this->findButtonByText($buttonText)->click();
    }

    private function findButtonByText(string $buttonText): RemoteWebElement
    {
        foreach ($this->findMany('button') as $element) {
            if ($element->getText() === $buttonText) {
                return $element;
            }
        }
        Assert::fail("Failed to locate button on page by text: $buttonText");
    }

    public function find(string $selector): RemoteWebElement
    {
        return $this->driver->findElement(WebDriverBy::cssSelector($selector));
    }

    public function currentUrl(): string
    {
        return $this->loadedPageUrl() ?? throw new \RuntimeException('The browser has not been navigated yet.');
    }

    public function loadedPageUrl(): ?string
    {
        $url = \parse_url($this->driver->getCurrentURL());
        if ($url['scheme'] === 'data') {
            return null;
        }
        return $url['path'];
    }

    public function clearCookies(): void
    {
        $this->driver->manage()->deleteAllCookies();
    }

    public function clearLocalStorage(): void
    {
        $this->currentUrl();
        $this->driver->executeScript('window.localStorage.clear();');
    }

    public function readInputValue(string $cssSelector): string
    {
        return $this->find($cssSelector)->getAttribute('value');
    }

    private function findMany(string $selector): array
    {
        return $this->driver->findElements(WebDriverBy::cssSelector($selector));
    }

    private function fitViewportToContent(): void
    {
        new Dusk\Browser($this->driver)->fitContent();
    }

    private function formatScreenshotPath(string $screenshotFilename): string
    {
        return \sPrintF('%s/%s.png', \rTrim($this->screenshotPath, '/'), $screenshotFilename);
    }
}
