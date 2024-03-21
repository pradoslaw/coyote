<?php
namespace Neon\Test\Unit\Navigation;

use Neon\Test\BaseFixture\Selector\Selector;
use PHPUnit\Framework\TestCase;

class NavigationViewTest extends TestCase
{
    use Fixture\ViewFixture;

    /**
     * @test
     */
    public function menuItems(): void
    {
        $view = $this->navigationView(['items' => ['Foo', 'Bar', 'Cat']]);
        $this->assertSame(
            ['Foo', 'Bar', 'Cat'],
            $this->texts($view,
                new Selector('html', 'body', 'header', 'nav', 'ul', 'li')));
    }

    /**
     * @test
     */
    public function controls(): void
    {
        $view = $this->navigationView(['controls' => ['Register', 'Login']]);
        $this->assertSame(
            ['Register', 'Login'],
            $this->texts($view,
                new Selector('ul.controls', 'li')));
    }

    /**
     * @test
     */
    public function githubName(): void
    {
        $view = $this->navigationView(['githubName' => 'Joe']);
        $this->assertSame('Joe',
            $this->text($view,
                new Selector('.github', '.name')));
    }

    /**
     * @test
     */
    public function githubStars(): void
    {
        $view = $this->navigationView(['githubStars' => '4']);
        $this->assertSame(
            '4',
            $this->text($view,
                new Selector('.github', '.stars')));
    }
}
