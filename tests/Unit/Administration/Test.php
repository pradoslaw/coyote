<?php
namespace Tests\Unit\Administration;

use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

class Test extends TestCase
{
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
     * @dataProvider searches
     */
    public function listUsersInAdministration(string $search): void
    {
        // given
        $this->existingUsers(['Carpet']);
        $this->userInAdministratorDashboard();
        // when
        $response = $this->searchByUsername($search);
        // then
        $this->assertUsersPresented($response, ['Carpet']);
    }

    public static function searches(): DataProvider
    {
        return DataProvider::list('Car', 'pet');
    }
}
