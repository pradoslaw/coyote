<?php
namespace Neon\Test\Unit\Navigation;

use Neon\View\Language\English;
use Neon\View\Language\Polish;
use PHPUnit\Framework\TestCase;

class NavigationViewModelLangTest extends TestCase
{
    use Fixture\ViewFixture;

    /**
     * @test
     */
    public function logout(): void
    {
        $view = $this->viewModel([], new English());
        $this->assertSame(
            'Logout',
            $view->logoutTitle);
    }

    /**
     * @test
     */
    public function logoutPl(): void
    {
        $view = $this->viewModel([], new Polish());
        $this->assertSame(
            'Wyloguj',
            $view->logoutTitle);
    }
}
