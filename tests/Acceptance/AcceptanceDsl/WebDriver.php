<?php
namespace Tests\Acceptance\AcceptanceDsl;

use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use PHPUnit\Framework\Assert;

readonly class WebDriver
{
    private string $baseUrl;

    public function __construct(
        public RemoteWebDriver $driver,
        private string         $screenshotPath,
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

    public function screenshotFit(string $screenshotFilename, ?int $width = null): void
    {
        $this->enlargeToContent($width);
        \uSleep(300 * 1000);
        $this->screenshot($screenshotFilename);
    }

    public function screenshot(string $screenshotFilename): void
    {
        $this->driver->takeScreenshot($this->formatScreenshotPath($screenshotFilename));
    }

    public function screenshotElement(string $filename, string $cssSelector): void
    {
        $this->screenshotSeleniumElement($filename, $this->find($cssSelector));
    }

    public function screenshotSeleniumElement(string $filename, RemoteWebElement $element): void
    {
        $element->takeElementScreenshot($this->formatScreenshotPath($filename));
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
        try {
            $this->findButtonByText($buttonText)->click();
        } catch (ElementClickInterceptedException $exception) {
            $this->driver->takeScreenshot($this->formatScreenshotPath('ElementClickInterceptedException'));
            throw $exception;
        }
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
        try {
            return $this->driver->findElement(WebDriverBy::cssSelector($selector));
        } catch (NoSuchElementException $exception) {
            $this->driver->takeScreenshot($this->formatScreenshotPath('NoSuchElementException'));
            throw $exception;
        }
    }

    public function findByText(string $text): RemoteWebElement
    {
        if (\str_contains($text, "'")) {
            throw new \RuntimeException('Quoting link text not supported yet.');
        }
        return $this->driver->findElement(WebDriverBy::xpath("//*/text()[normalize-space(.)='$text']/.."));
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

    public function enlargeToContent(?int $width): void
    {
        $size = $this->driver->findElement(WebDriverBy::tagName('html'))->getSize();
        $this->resize($width ?? $size->getWidth(), $size->getHeight());
    }

    public function resize(int $width, int $height): void
    {
        $this->driver->manage()->window()->setSize(new WebDriverDimension($width, $height));
    }

    public function formatScreenshotPath(string $screenshotFilename): string
    {
        return \sPrintF('%s/%s.png', \rTrim($this->screenshotPath, '/'), $screenshotFilename);
    }

    public function disableCssTransitions(): void
    {
        $this->driver->executeScript('
            const style = document.createElement("style")
            style.textContent = "* {transition:none!important;animation:none!important;-webkit-font-smoothing:none!important;text-rendering:optimizeSpeed!important;}"
            document.head.appendChild(style)
        ');
    }

    public function hideKeyboardCursor(): void
    {
        $this->driver->executeScript('document.activeElement.blur();');
    }

}
