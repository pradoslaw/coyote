<?php
namespace Tests\LookAndFeel\Theme;

use Tests\Acceptance\AcceptanceDsl\WebDriver;

readonly class LookAndFeelToggle
{
    public function __construct(private WebDriver $webDriver) {}

    public function setTheme(bool $modern, bool $dark): void
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
                if (window.document.querySelector('.navbar')) {
                    window.document.querySelector('.navbar').classList.toggle('navbar-light', !isDark);
                    window.document.querySelector('.navbar').classList.toggle('navbar-dark', isDark);
                }
                if (window.document.querySelector('.logo')) {
                    window.document.querySelector('.logo').src = isDark 
                        ? (isModern ? '/img/logo-modern.svg' : '/img/logo-dark.svg') 
                        : (isModern ? '/img/logo-modern.svg' : '/img/logo-light.svg');
                }
            }
            
            function disableCssTransitions() {
                const style = document.createElement('style');
                style.type = 'text/css';
                style.appendChild(document.createTextNode('* {transition: none !important;}'));
                document.head.appendChild(style);
            }
            
            disableCssTransitions();
            setTheme(isModern, isDark);
        ", [$modern, $dark]);
    }
}
