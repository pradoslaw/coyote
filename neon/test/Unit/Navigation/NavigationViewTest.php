<?php
namespace Neon\Test\Unit\Navigation;

use PHPUnit\Framework\TestCase;

class NavigationViewTest extends TestCase
{
    use Fixture\ViewFixture;

    /**
     * @test
     */
    public function homepage(): void
    {
        $view = $this->navigation(['homepageUrl' => 'http://homepage/']);
        $this->assertSame(
            'http://homepage/',
            $view->find('#homepage', '@href'));
    }

    /**
     * @test
     */
    public function menuItems(): void
    {
        $view = $this->navigation(['items' => ['Foo' => '', 'Bar' => '']]);
        $this->assertSame(
            ['Foo', 'Bar'],
            $view->findTextMany('nav', 'ul.menu-items', 'li', 'a'));
    }

    /**
     * @test
     */
    public function menuItemLinks(): void
    {
        $view = $this->navigation(['items' => [
            'Foo' => 'foo.png', 'Bar' => 'bar.jpg',
        ]]);
        $this->assertSame(
            ['foo.png', 'bar.jpg'],
            $view->findMany('nav', 'ul.menu-items', 'li', 'a', '@href'));
    }

    /**
     * @test
     */
    public function controls(): void
    {
        $view = $this->navigation(['controls' => ['Register' => '', 'Login' => '']]);
        $this->assertSame(
            ['Register', 'Login'],
            $view->findTextMany('ul.controls', 'li', 'a'));
    }

    /**
     * @test
     */
    public function controlsLinks(): void
    {
        $view = $this->navigation(['controls' => [
            'Register' => '/account',
            'Login'    => '/login',
        ]]);
        $this->assertSame(
            ['/account', '/login'],
            $view->findMany('ul.controls', 'li', 'a', '@href'));
    }

    /**
     * @test
     */
    public function noControlsLinksLoggedIn(): void
    {
        $view = $this->navigation([
            'controls'          => ['Control' => '/'],
            'loggedInAvatarUrl' => 'foo.png',
        ]);
        $this->assertSame(
            [],
            $view->findTextMany('ul.controls', 'li', 'a', '@href'));
    }

    /**
     * @test
     */
    public function githubName(): void
    {
        $view = $this->navigation(['githubName' => 'Joe']);
        $this->assertSame(
            'Joe',
            $view->findText('.github', '.name'));
    }

    /**
     * @test
     */
    public function githubUrl(): void
    {
        $view = $this->navigation(['githubUrl' => 'http://github.com/Foo']);
        $this->assertSame(
            'http://github.com/Foo',
            $view->find('.github', 'a.name', '@href'));
    }

    /**
     * @test
     */
    public function githubStars(): void
    {
        $view = $this->navigation(['githubStars' => '4']);
        $this->assertSame(
            '4',
            $view->findText('.github', '.stars'));
    }

    /**
     * @test
     */
    public function githubStarsUrl(): void
    {
        $view = $this->navigation(['githubStarsUrl' => 'http://github.com/Bar']);
        $this->assertSame(
            'http://github.com/Bar',
            $view->find('.github', 'a.stars', '@href'));
    }

    /**
     * @test
     */
    public function registerButtonBold(): void
    {
        $view = $this->navigation(['controls' => [
            'Register' => '/account',
            'Login'    => '/login',
        ]]);
        $this->assertContains(
            'rounded',
            $view->cssClasses('ul.controls', 'li[1]'));
    }

    /**
     * @test
     */
    public function loginButtonRegular(): void
    {
        $view = $this->navigation(['controls' => [
            'Register' => '/account',
            'Login'    => '/login',
        ]]);
        $this->assertNotContains(
            'rounded',
            $view->cssClasses('ul.controls', 'li[2]'));
    }

    /**
     * @test
     */
    public function userAvatar(): void
    {
        $view = $this->navigation(['loggedInAvatarUrl' => '/avatar.png']);
        $this->assertSame(
            '/avatar.png',
            $view->find('header', '#userAvatar', '@src'));
    }

    /**
     * @test
     */
    public function logoutButtonUser(): void
    {
        $view = $this->navigation(['loggedIn' => true]);
        $this->assertSame(
            'Logout',
            $view->findText('#logout'));
    }

    /**
     * @test
     */
    public function logoutButtonGuest(): void
    {
        $view = $this->navigation([]);
        $this->assertSame(
            [],
            $view->findTextMany('#logout'));
    }
}
