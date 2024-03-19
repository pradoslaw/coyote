<?php
namespace Neon\Test\Unit\View;

use PHPUnit\Framework\TestCase;

class PageViewTest extends TestCase
{
    use Fixture\ViewFixture;

    /**
     * @test
     */
    public function breadcrumbs(): void
    {
        $view = $this->view(['applicationName' => 'Greyjoy']);
        $this->assertSame(
            ['Greyjoy', 'Events'],
            $this->texts($view, '/html/body/nav/ul/li'));
    }

    /**
     * @test
     */
    public function title(): void
    {
        $view = $this->view(['sectionTitle' => 'Ours is the Fury']);
        $this->assertSame(
            'Ours is the Fury',
            $this->text($view, '/html/body/h1'));
    }
}
