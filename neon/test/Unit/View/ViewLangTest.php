<?php
namespace Neon\Test\Unit\View;

use Neon\Domain\Attendance;
use Neon\Test\Unit\Navigation\Fixture\LoggedInUser;
use Neon\View\Language\Polish;
use Neon\View\View;
use PHPUnit\Framework\TestCase;

class ViewLangTest extends TestCase
{
    use Fixture\ViewFixture;

    /**
     * @test
     */
    public function sectionTitle(): void
    {
        $view = $this->view();
        $this->assertSame(
            'Nadchodzące wydarzenia',
            $this->viewSectionTitle($view));
    }

    /**
     * @test
     */
    public function subsectionTitle(): void
    {
        $this->assertSame(
            'Wydarzenia z naszym patronatem',
            $this->viewSubsectionTitle($this->view()));
    }

    /**
     * @test
     */
    public function breadcrumbs(): void
    {
        $view = $this->view(['applicationName' => 'Greyjoy']);
        $this->assertSame(
            ['Greyjoy', 'Wydarzenia'],
            $this->viewSectionBreadcrumbs($view));
    }

    /**
     * @test
     */
    public function navigationItems(): void
    {
        $this->assertSame(
            ['Forum', 'Mikroblogi', 'Praca', 'Kompendium'],
            $this->viewNavigationItems($this->view()));
    }

    /**
     * @test
     */
    public function headerControls(): void
    {
        $this->assertSame(
            ['Utwórz konto', 'Logowanie'],
            $this->viewHeaderControls($this->view()));
    }

    /**
     * @test
     */
    public function attendanceTotalTitle(): void
    {
        $this->assertSame(
            ['Użytkowników'],
            $this->findMany($this->view(), '#attendance', '#totalTitle'));
    }

    function view(array $fields = []): View
    {
        return new View(
            new Polish(),
            $fields['applicationName'] ?? '',
            [],
            new Attendance(0, 0),
            [],
            LoggedInUser::guest(),
            '');
    }
}
