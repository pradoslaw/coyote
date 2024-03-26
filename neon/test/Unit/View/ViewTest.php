<?php
namespace Neon\Test\Unit\View;

use Neon\Domain\Attendance;
use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @test
     */
    public function sectionTitle(): void
    {
        $dom = $this->dom();
        $this->assertSame(
            'Incoming events',
            $dom->find('//main//h1/text()'));
    }

    /**
     * @test
     */
    public function breadcrumbs(): void
    {
        $dom = $this->dom(['applicationName' => 'Greyjoy']);
        $this->assertSame(
            ['Greyjoy', 'Events'],
            $dom->findMany('/html/body//nav/ul/li/text()'));
    }

    /**
     * @test
     */
    public function navigationItems(): void
    {
        $this->assertSame(
            ['Forum', 'Microblogs', 'Jobs', 'Wiki',],
            $this->findMany('nav', 'ul.menu-items', 'li', 'a'));
    }

    /**
     * @test
     */
    public function navigationItemsLinks(): void
    {
        $this->assertSame(
            ['/Forum', '/Mikroblogi', '/Praca', '/Kategorie'],
            $this->findMany('nav', 'ul.menu-items', 'li', 'a', '@href'));
    }

    /**
     * @test
     */
    public function githubTitle(): void
    {
        $this->assertSame(
            'Coyote',
            $this->find('.github', '.name'));
    }

    /**
     * @test
     */
    public function githubUrl(): void
    {
        $this->assertSame(
            'https://github.com/pradoslaw/coyote',
            $this->find('.github', '.name', '@href'));
    }

    /**
     * @test
     */
    public function githubStars(): void
    {
        $this->assertSame(
            '111',
            $this->find('.github', '.stars'));
    }

    /**
     * @test
     */
    public function headerControls(): void
    {
        $this->assertSame(
            ['Create account', 'Login'],
            $this->findMany('ul.controls', 'li', 'a'));
    }

    /**
     * @test
     */
    public function favicon(): void
    {
        $this->assertSame(
            '<link rel="shortcut icon" href="https://4programmers.net/img/favicon.png" type="image/png">',
            $this->dom()->html('/html/head/link[@rel="shortcut icon"]'));
    }

    private function findMany(string...$selectors): array
    {
        $selector = new Selector(...$selectors);
        return $this->dom()->findMany($selector->xPath());
    }

    private function find(string...$selectors): string
    {
        $selector = new Selector(...$selectors);
        return $this->dom()->find($selector->xPath());
    }

    private function dom(array $fields = []): ViewDom
    {
        $view = new View(
            $fields['applicationName'] ?? '',
            [],
            new Attendance(0, 0));
        return new ViewDom($view->html());
    }
}
