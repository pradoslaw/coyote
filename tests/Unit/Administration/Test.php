<?php
namespace Tests\Unit\Administration;

use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    use Fixture\AdministratorPanel;

    /**
     * @test
     */
    public function administratorCanSeeAdministratorLoginPanel(): void
    {
        // given
        $this->userIsAdministrator();
        // when
        $response = $this->administrationPanelIsOpened();
        // then
        $this->assertLoginPromptIsPresented($response);
    }

    /**
     * @test
     */
    public function canLoginToAdministratorPanel(): void
    {
        // given
        $this->userIsAdministrator();
        // when
        $this->userPassesLoginPrompt();
        // then
        $this->assertCanAccessAdministratorDashboard();
    }
}
