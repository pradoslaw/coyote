<?php
namespace Neon\Test\Unit\View;

use Neon;
use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\TestCase;

class PageViewTest extends TestCase
{
    use Fixture\ViewFixture;

    /**
     * @test
     */
    public function breadcrumbs(): void
    {
        $view = $this->view(['root' => 'Greyjoy']);
        $this->assertSame(
            ['Greyjoy', 'Events'],
            $this->texts($view, '/html/body//nav/ul/li/text()'));
    }

    /**
     * @test
     */
    public function title(): void
    {
        $view = $this->view(['sectionTitle' => 'Ours is the Fury']);
        $this->assertSame(
            'Ours is the Fury',
            $this->text($view, '/html/body//h1/text()'));
    }

    /**
     * @test
     */
    public function twoSections(): void
    {
        $view = new Neon\View([], [
            new Neon\View\Section('', 'Foo', []),
            new Neon\View\Section('', 'Bar', []),
        ]);
        $this->assertSectionTitles(['Foo', 'Bar'], $view);
    }

    private function assertSectionTitles(array $array, Neon\View $view): void
    {
        $dom = new ViewDom($view->html());
        $this->assertSame($array, $dom->findMany('/html/body//h1/text()'));
    }
}
