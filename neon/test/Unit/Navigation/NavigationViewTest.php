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
        $view = $this->navigationView(['items' => ['Foo' => '', 'Bar' => '']]);
        $this->assertSame(
            ['Foo', 'Bar'],
            $this->texts($view,
                new Selector('nav', 'ul.menu-items', 'li', 'a')));
    }

    /**
     * @test
     */
    public function menuItemLinks(): void
    {
        $view = $this->navigationView(['items' => [
            'Foo' => 'foo.png', 'Bar' => 'bar.jpg',
        ]]);
        $this->assertSame(
            ['foo.png', 'bar.jpg'],
            $this->texts($view,
                new Selector('nav', 'ul.menu-items', 'li', 'a', '@href')));
    }

    /**
     * @test
     */
    public function controls(): void
    {
        $view = $this->navigationView(['controls' => ['Register' => '', 'Login' => '']]);
        $this->assertSame(
            ['Register', 'Login'],
            $this->texts($view,
                new Selector('ul.controls', 'li', 'a')));
    }

    /**
     * @test
     */
    public function controlsLinks(): void
    {
        $view = $this->navigationView(['controls' => [
            'Register' => '/account',
            'Login'    => '/login',
        ]]);
        $this->assertSame(
            ['/account', '/login'],
            $this->texts($view,
                new Selector('ul.controls', 'li', 'a', '@href')));
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
