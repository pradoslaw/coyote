<?php
namespace Tests\LookAndFeel\Theme;

use Closure;
use Tests\Acceptance\AcceptanceDsl\WebDriver;

class Property
{
    private LookAndFeelToggle $toggle;

    public function __construct(
        private WebDriver $webDriver,
        private Closure   $producer,
        private string    $cssProperty,
    )
    {
        $this->toggle = new LookAndFeelToggle($this->webDriver);
    }

    public function inMode(string $lookAndFeel, string $theme): RenderedElement
    {
        if (!\in_array($lookAndFeel, ['modern', 'legacy'])) {
            throw new \RuntimeException("Invalid look&feel: $lookAndFeel");
        }
        if (!\in_array($theme, ['dark', 'light'])) {
            throw new \RuntimeException("Invalid theme: $theme");
        }
        $this->toggle->setTheme($lookAndFeel === 'modern', $theme === 'dark');
        $value = $this->readCssProperty($this->cssProperty);
        return new RenderedElement($this->formatColor($value));
    }

    private function readCssProperty(string $cssProperty): string
    {
        return ($this->producer)($this->webDriver)->getCSSValue($cssProperty);
    }

    private function formatColor(string $rgbColor): string
    {
        return new StyleGuide()->findNameByColor($rgbColor);
    }
}
