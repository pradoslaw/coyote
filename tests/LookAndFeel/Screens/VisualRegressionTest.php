<?php
namespace Tests\LookAndFeel\Screens;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Acceptance\AcceptanceDsl\Driver;
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

    #[After]
    public function afterTest(): void
    {
        self::$runner->webDriver->clearCookies();
        self::$runner->webDriver->clearLocalStorage();
    }

    #[AfterClass]
    public static function finishSuite(): void
    {
        self::$runner->finishSuite();
        $imageProcess = new ImageProcess('/var/www/tests/LookAndFeel/Screens/');
        $screens = [
            'screen.homepage.png',
            'screen.categories.png',
            'screen.topicList.png',
            'screen.topic.png',
            'screen.microblogs.png',
            'screen.profile.png',
            'screen.jobBoard.png',
            'screen.register.png',
            'screen.login.png',
            'screen.reputationHomepage.png',
            'screen.reputationAccount.png',
            'screen.reputationProfile.png',
            'screen.reputationVCard.png',
        ];
        $imageProcess->mergeVertically($screens, 'screens.png');
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
        $this->navigate('/Forum/Python/1', 800);
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

    #[Test]
    #[DoesNotPerformAssertions]
    public function reputationVCard(): void
    {
        $this->navigate('/');
        $this->showVCardUnder('.microblog .username', '#vcard');
        $this->captureScreenshots('#vcard');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function reputationHomepage(): void
    {
        $this->navigate('/');
        $this->captureScreenshots('.card-reputation');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function reputationAccount(): void
    {
        $this->navigate('/');
        $dsl = new Driver(self::$runner->webDriver);
        $dsl->loginUser('user', 'user');
        self::$runner->webDriver->navigate('/User');
        $this->captureScreenshots('#box-start');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function reputationProfile(): void
    {
        $this->navigate('/Profile/1');
        $this->captureScreenshots(element:$this->findByChildText('Historia reputacji'));
    }

    private function visit(string $url): void
    {
        $this->navigate($url);
        $this->captureScreenshots();
    }

    private function navigate(string $url, ?string $viewportWidth = null): void
    {
        self::$runner->webDriver->navigate($url);
        self::$runner->webDriver->disableCssTransitions();
        self::$runner->webDriver->hideKeyboardCursor();
        self::$runner->webDriver->driver->executeScript("if (document.querySelector('.execution-time')) document.querySelector('.execution-time').remove();");
        $this->closeGdpr();
        $this->changeViewportSize($viewportWidth ?? 1200);
    }

    private function changeViewportSize(?int $width): void
    {
        self::$runner->webDriver->resize($width ?? 1200, 300);
        self::$runner->webDriver->enlargeToContent($width ?? 1200);
    }

    private function captureScreenshots(?string $elementCssSelector = null, ?RemoteWebElement $element = null): void
    {
        $names = [];
        foreach ([false, true] as $dark) {
            foreach ([false, true] as $modern) {
                $filename = $this->screenshotFilename($modern, $dark);
                $this->setTheme($modern, $dark);
                if ($element) {
                    self::$runner->webDriver->screenshotSeleniumElement($filename, $element);
                } else if ($elementCssSelector) {
                    self::$runner->webDriver->screenshotElement($filename, $elementCssSelector);
                } else {
                    self::$runner->webDriver->screenshot($filename);
                }
                $names[] = "$filename.png";
            }
        }
        new ImageProcess('/var/www/tests/LookAndFeel/Screens/')->mergeHorizontally($names, 'screen.' . $this->name() . '.png');
        self::$runner->clear('tmp.*.png');
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

    private function showVCardUnder(string $selector, string $vcardSelector): void
    {
        $element = self::$runner->webDriver->find($selector);
        $this->hoverOver($element);
        self::$runner->webDriver->driver->wait()->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector($vcardSelector)));
    }

    private function hoverOver(RemoteWebElement $element): void
    {
        $actions = new WebDriverActions(self::$runner->webDriver->driver);
        $actions->moveToElement($element)->perform();
    }

    private function findByChildText(string $text): RemoteWebElement
    {
        $child = self::$runner->webDriver->findByText($text);
        return $child->findElement(WebDriverBy::xpath('..'));
    }
}
