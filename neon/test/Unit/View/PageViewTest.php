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
        $view = $this->view('Greyjoy');
        $this->assertSame(
            ['Greyjoy', 'Events'],
            $this->texts($view, '/html/body/nav/ul/li'));
    }
}
