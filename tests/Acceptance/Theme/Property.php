<?php
namespace Tests\Acceptance\Theme;

use Closure;
use Tests\Acceptance\AcceptanceDsl\WebDriver;

class Property
{
    public function __construct(
        private WebDriver $webDriver,
        private Closure   $producer,
        private string    $cssProperty,
    ) {}

    public function inMode(string $lookAndFeel, string $theme): RenderedElement
    {
        if (!\in_array($lookAndFeel, ['modern', 'legacy'])) {
            throw new \RuntimeException("Invalid look&feel: $lookAndFeel");
        }
        if (!\in_array($theme, ['dark', 'light'])) {
            throw new \RuntimeException("Invalid theme: $theme");
        }
        $this->setTheme($lookAndFeel, $theme);
        $value = $this->readCssProperty($this->cssProperty);
        return new RenderedElement($this->formatColor($value));
    }

    private function readCssProperty(string $cssProperty): string
    {
        return ($this->producer)($this->webDriver)->getCSSValue($cssProperty);
    }

    private function setTheme(string $lookAndFeel, string $darkTheme): void
    {
        $this->webDriver->driver->executeScript("
            const [isModern, isDark] = arguments;

            function setTheme(isModern, isDark) {
                window.document.documentElement.classList.toggle('theme-light', !isDark);
                window.document.documentElement.classList.toggle('theme-dark', isDark);
                window.document.body.classList.toggle('theme-light', !isDark);
                window.document.body.classList.toggle('theme-dark', isDark);
                window.document.body.classList.toggle('look-and-feel-legacy', !isModern);
                window.document.body.classList.toggle('look-and-feel-modern', isModern);
                window.document.querySelector('.navbar').classList.toggle('navbar-light', !isDark);
                window.document.querySelector('.navbar').classList.toggle('navbar-dark', isDark);
                window.document.querySelector('.logo').src = isDark 
                    ? (isModern ? '/img/logo-modern.svg' : '/img/logo-dark.svg') 
                    : (isModern ? '/img/logo-modern.svg' : '/img/logo-light.svg');
            }
            
            function disableCssTransitions() {
                const style = document.createElement('style');
                style.type = 'text/css';
                style.appendChild(document.createTextNode('* {transition: none !important;}'));
                document.head.appendChild(style);
            }
            
            disableCssTransitions();
            setTheme(isModern, isDark);
        ", [$lookAndFeel === 'modern', $darkTheme === 'dark']);
    }

    private function formatColor(string $rgbColor): string
    {
        return new StyleGuide()->findNameByColor($rgbColor);
    }
}
