<?php
namespace Neon\Test\Unit\Navigation;

use Neon\Test\BaseFixture\Selector\Selector;
use Neon\View\HtmlView;
use PHPUnit\Framework\TestCase;

class NavigationViewTest extends TestCase
{
    use Fixture\ViewFixture;

    /**
     * @test
     */
    public function homepage(): void
    {
        $view = $this->navigationView(['homepageUrl' => 'http://homepage/']);
        $this->assertSame(
            'http://homepage/',
            $this->text($view,
                new Selector('#homepage', '@href')));
    }

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
    public function githubUrl(): void
    {
        $view = $this->navigationView(['githubUrl' => 'http://github.com/Foo']);
        $this->assertSame('http://github.com/Foo',
            $this->text($view,
                new Selector('.github', 'a.name', '@href')));
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

    /**
     * @test
     */
    public function githubStarsUrl(): void
    {
        $view = $this->navigationView(['githubStarsUrl' => 'http://github.com/Bar']);
        $this->assertSame('http://github.com/Bar',
            $this->text($view,
                new Selector('.github', 'a.stars', '@href')));
    }

    /**
     * @test
     */
    public function registerButtonBold(): void
    {
        $view = $this->navigationView(['controls' => [
            'Register' => '/account',
            'Login'    => '/login',
        ]]);
        $this->assertContains('rounded',
            $this->cssClasses($view, ['ul.controls', 'li[1]']));
    }

    /**
     * @test
     */
    public function loginButtonRegular(): void
    {
        $view = $this->navigationView(['controls' => [
            'Register' => '/account',
            'Login'    => '/login',
        ]]);
        $this->assertNotContains('rounded',
            $this->cssClasses($view, ['ul.controls', 'li[2]']));
    }

    private function cssClasses(HtmlView $view, array $selectors): array
    {
        $xPathSelectors = \array_merge($selectors, ['@class']);
        $classAttribute = $this->text($view, new Selector(...$xPathSelectors));
        return \explode(' ', $classAttribute);
    }

    /**
     * @test
     */
    public function userAvatar(): void
    {
        $view = $this->navigationView(['loggedInAvatarUrl' => '/avatar.png']);
        $this->assertSame('/avatar.png',
            $this->text($view,
                new Selector('header', '#userAvatar', '@src')));
    }
}
