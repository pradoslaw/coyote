<?php
namespace Tests\Acceptance\Theme;

use Facebook\WebDriver\Remote\RemoteWebElement;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Acceptance\AcceptanceDsl\TestRunner;
use Tests\Acceptance\AcceptanceDsl\WebDriver;

class ThemeTest extends TestCase
{
    private static TestRunner $runner;

    #[BeforeClass]
    public static function initializeTestRunner(): void
    {
        self::$runner = new TestRunner('/var/www/tests/Acceptance/Theme/');
        self::$runner->clearScreenshots();
        self::$runner->webDriver->navigate('/');
        self::$runner->webDriver->find('#gdpr-all')->click();
    }

    #[AfterClass]
    public static function finishSuite(): void
    {
        self::$runner->finishSuite();
    }

    #[Test]
    public function bodyBackground(): void
    {
        $bodyBackground = $this->background('body');
        $bodyBackground->inMode('modern', 'light')->is('navy-050');
        $bodyBackground->inMode('modern', 'dark')->is('neutral-900');
        $bodyBackground->inMode('legacy', 'light')->is('#fafafa');
        $bodyBackground->inMode('legacy', 'dark')->is('#252525');
    }

    #[Test]
    public function microblogTileBackground(): void
    {
        $bodyBackground = $this->background('.card-reputation');
        $bodyBackground->inMode('modern', 'light')->is('white');
        $bodyBackground->inMode('modern', 'dark')->is('neutral-800');
        $bodyBackground->inMode('legacy', 'light')->is('white');
        $bodyBackground->inMode('legacy', 'dark')->is('#1a1a1a');
    }

    #[Test]
    public function reputationTileBackground(): void
    {
        $bodyBackground = $this->background('.microblog');
        $bodyBackground->inMode('modern', 'light')->is('white');
        $bodyBackground->inMode('modern', 'dark')->is('neutral-800');
        $bodyBackground->inMode('legacy', 'light')->is('white');
        $bodyBackground->inMode('legacy', 'dark')->is('#1a1a1a');
    }

    #[Test]
    public function homepageNewsActiveTabColor(): void
    {
        $activeBorder = $this->borderColor('Najciekawsze');
        $activeBorder->inMode('modern', 'light')->is('green-500');
        $activeBorder->inMode('modern', 'dark')->is('green-500');
        $activeBorder->inMode('legacy', 'light')->is('#d7661c');
        $activeBorder->inMode('legacy', 'dark')->is('#d7661c');
    }

    #[Test]
    public function homepageReputationActiveTabColor(): void
    {
        $activeBorder = $this->borderColor('W tym tygodniu', parent:true);
        $activeBorder->inMode('modern', 'light')->is('green-500');
        $activeBorder->inMode('modern', 'dark')->is('green-500');
        $activeBorder->inMode('legacy', 'light')->is('#d7661c');
        $activeBorder->inMode('legacy', 'dark')->is('#d7661c');
    }

    private function background(string $cssSelector): Property
    {
        return $this->property('background-color',
            fn(WebDriver $driver) => $driver->find($cssSelector));
    }

    private function borderColor(string $text, bool $parent = false): Property
    {
        return $this->property('border-bottom-color',
            $this->find($text, null, null, $parent));
    }

    private function find(?string $text, ?string $htmlTag, ?string $cssClass, ?bool $parent): callable
    {
        return function (WebDriver $driver) use ($text, $htmlTag, $cssClass, $parent): RemoteWebElement {
            $xPath = '//';
            $xPath .= $htmlTag ?? '*';
            $xPath .= $text ? "[normalize-space()='$text']" : '';
            $xPath .= $cssClass ? "[contains(@class, '$cssClass')]" : '';
            $xPath .= $parent ? '/parent::*' : '';
            return $driver->findByXPath($xPath);
        };
    }

    private function property(string $cssProperty, callable $producer): Property
    {
        return new Property(self::$runner->webDriver, $producer, $cssProperty);
    }
}
