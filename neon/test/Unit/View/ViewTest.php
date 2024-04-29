<?php
namespace Neon\Test\Unit\View;

use Neon\Domain\Attendance;
use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\Unit\Navigation\Fixture\LoggedInUser;
use Neon\View\Language\English;
use Neon\View\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    use Fixture\ViewFixture;

    /**
     * @test
     */
    public function sectionTitle(): void
    {
        $this->assertSame(
            'Incoming events',
            $this->viewSectionTitle($this->view()));
    }

    /**
     * @test
     */
    public function subsectionTitle(): void
    {
        $this->assertSame(
            'Events with our patronage',
            $this->viewSubsectionTitle($this->view()));
    }

    /**
     * @test
     */
    public function breadcrumbs(): void
    {
        $view = $this->view(['applicationName' => 'Greyjoy']);
        $this->assertSame(
            ['Greyjoy', 'Events'],
            $this->viewSectionBreadcrumbs($view));
    }

    /**
     * @test
     */
    public function navigationItems(): void
    {
        $this->assertSame(
            ['Forum', 'Microblogs', 'Jobs', 'Wiki', 'Events'],
            $this->findTextMany($this->view(), 'nav', 'ul.menu-items', 'li', 'a'));
    }

    /**
     * @test
     */
    public function navigationItemsLinks(): void
    {
        $this->assertSame(
            ['/Forum', '/Mikroblogi', '/Praca', '/Kategorie', '/events'],
            $this->findMany($this->view(), 'nav', 'ul.menu-items', 'li', 'a', '@href'));
    }

    /**
     * @test
     */
    public function githubTitle(): void
    {
        $this->assertSame(
            'Coyote',
            $this->findText($this->view(), '.github', '.name'));
    }

    /**
     * @test
     */
    public function githubUrl(): void
    {
        $this->assertSame(
            'https://github.com/pradoslaw/coyote',
            $this->find($this->view(), '.github', '.name', '@href'));
    }

    /**
     * @test
     */
    public function githubStars(): void
    {
        $this->assertSame(
            '112',
            $this->findText($this->view(), '.github', '.stars'));
    }

    /**
     * @test
     */
    public function headerControls(): void
    {
        $this->assertSame(
            ['Create account', 'Login'],
            $this->findTextMany($this->view(), 'ul.controls', 'li', 'a'));
    }

    /**
     * @test
     */
    public function attendanceTotalTitle(): void
    {
        $this->assertSame(
            'Users',
            $this->findText($this->view(), '#attendance', '#totalTitle'));
    }

    /**
     * @test
     */
    public function favicon(): void
    {
        $this->assertSame(
            '<link rel="shortcut icon" href="https://4programmers.net/img/favicon.png" type="image/png">',
            $this->dom($this->view())->html('/html/head/link[@rel="shortcut icon"]'));
    }

    function view(array $fields = []): View
    {
        return new View(
            new English(),
            $fields['applicationName'] ?? '',
            [],
            new Attendance(0, 0),
            [],
            LoggedInUser::guest(),
            '',
            false);
    }

    private function find(View $view, string...$selectors): string
    {
        $selector = new Selector(...$selectors);
        return $this->dom($view)->findString($selector->xPath());
    }

    private function findText(View $view, string...$selectors): string
    {
        $selector = new Selector(...\array_merge($selectors, ['text()']));
        return $this->dom($view)->findString($selector->xPath());
    }
}
