<?php
namespace Tests\LookAndFeel\Screens;

use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Acceptance\AcceptanceDsl\TestRunner;
use Tests\LookAndFeel\Theme\LookAndFeelToggle;

class VisualRegressionTest extends TestCase
{
    private static TestRunner $runner;

    #[BeforeClass]
    public static function initializeTestRunner(): void
    {
        self::$runner = new TestRunner('/var/www/tests/LookAndFeel/Screens/');
        self::$runner->clearScreenshots();
    }

    #[AfterClass]
    public static function finishSuite(): void
    {
        self::$runner->finishSuite();
//        $inputs = [
//            'screen.homepage.png',
//            'screen.categories.png',
//            'screen.topicList.png',
//            'screen.topic.png',
//            'screen.microblogs.png',
//            'screen.profile.png',
//            'screen.jobBoard.png',
//            'screen.register.png',
//            'screen.login.png',
//        ];
//        new ImageProcess('/var/www/tests/LookAndFeel/Screens/')->mergeVertically($inputs, 'screens.png');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function homepage(): void
    {
        $this->visit('/');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function microblogs(): void
    {
        $this->visit('/Mikroblogi');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function categories(): void
    {
        $this->visit('/Forum/Categories');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function topicList(): void
    {
        $this->visit('/Forum/All');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function topic(): void
    {
        $this->visit('/Forum/Python/1');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function topicSidebar(): void
    {
        $this->navigate('/Forum/Python/1');
        $this->changeViewportSize(800);
        $this->openSidebar();
        $this->captureScreenshots();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function login(): void
    {
        $this->visit('/Login');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function register(): void
    {
        $this->visit('/Register');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function profile(): void
    {
        $this->visit('/Profile/1');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function jobBoard(): void
    {
        $this->visit('/Praca');
    }

    private function visit(string $url): void
    {
        $this->navigate($url);
        $this->changeViewportSize(1200);
        $this->captureScreenshots();
    }

    private function navigate(string $url): void
    {
        self::$runner->webDriver->navigate($url);
        self::$runner->webDriver->disableCssTransitions();
        self::$runner->webDriver->hideKeyboardCursor();
        self::$runner->webDriver->driver->executeScript("if (document.querySelector('.execution-time')) document.querySelector('.execution-time').remove();");
        $this->closeGdpr();
    }

    private function changeViewportSize(?int $width): void
    {
        self::$runner->webDriver->resize($width ?? 1200, 300);
        self::$runner->webDriver->enlargeToContent($width ?? 1200);
    }

    private function captureScreenshots(): void
    {
        $names = [];
        foreach ([false, true] as $dark) {
            foreach ([false, true] as $modern) {
                $filename = $this->screenshotFilename($modern, $dark);
                $this->setTheme($modern, $dark);
                self::$runner->webDriver->screenshot($filename);
                $names[] = "$filename.png";
            }
        }
//        new ImageProcess('/var/www/tests/LookAndFeel/Screens/')->mergeHorizontally($names, 'screen.' . $this->name() . '.png');
//        self::$runner->clear('tmp.*.png');
    }

    private function screenshotFilename(bool $modern, bool $dark): string
    {
        return \implode('.', ['tmp', $this->name(), $modern ? 'modern' : 'legacy', $dark ? 'dark' : 'light']);
    }

    private function setTheme(bool $modern, bool $dark): void
    {
        $toggle = new LookAndFeelToggle(self::$runner->webDriver);
        $toggle->setTheme($modern, $dark);
    }

    private function closeGdpr(): void
    {
        $gdpr = self::$runner->webDriver->find('#gdpr-all');
        if ($gdpr->isDisplayed()) {
            $gdpr->click();
        }
    }

    private function openSidebar(): void
    {
        self::$runner->webDriver->find('#btn-toggle-sidebar')->click();
    }
}
