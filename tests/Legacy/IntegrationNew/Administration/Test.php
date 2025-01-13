<?php
namespace Tests\Legacy\IntegrationNew\Administration;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;

class Test extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use Fixture\AdministratorPanel;
    use Fixture\AdministratorPanelUsers;

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

    /**
     * @test
     */
    public function listUsersInAdministration(): void
    {
        // given
        $this->existingUsers(['Lorem']);
        $this->userInAdministratorDashboard();
        // when
        $response = $this->searchByUsername('ore');
        // then
        $this->assertContains('Lorem', $this->userNames($response));
    }

    /**
     * @test
     */
    public function wildcardInPlace(): void
    {
        // given
        $this->existingUsers(['Home', 'Meow']);
        $this->userInAdministratorDashboard();
        // when
        $response = $this->searchByUsername('me*');
        // then
        $this->assertNotContains(['Home'], $this->userNames($response));
    }

    /**
     * @test
     */
    public function exactSearch(): void
    {
        // given
        $this->existingUsers(['Food', 'Foo']);
        $this->userInAdministratorDashboard();
        // when
        $response = $this->searchByUsername('"Foo"');
        // then
        $this->assertNotContains(['Food'], $this->userNames($response));
    }
}
