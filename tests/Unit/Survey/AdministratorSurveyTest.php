<?php
namespace Tests\Unit\Survey;

use Coyote\Domain\Survey\AdministratorSurvey;
use Coyote\Domain\Survey\GuestSurvey;
use Coyote\Models\Survey;
use Coyote\Services\Guest;
use Coyote\User;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Forum\ModelsDsl;
use Tests\Unit\BaseFixture\Server\Laravel\Transactional;

class AdministratorSurveyTest extends TestCase
{
    use Transactional;

    private ModelsDsl $dsl;
    private AdministratorSurvey $admin;

    #[Before]
    public function initialize(): void
    {
        $this->dsl = new ModelsDsl();
        $this->admin = new AdministratorSurvey();
    }

    #[Test]
    public function countMembersNone(): void
    {
        $this->assertSame(0, $this->admin->membersCount($this->admin->newSurvey('')));
    }

    #[Test]
    public function countMembers(): void
    {
        // given
        $survey = $this->newSurveyWithMembers([$this->dsl->newUserReturnId()]);
        // when
        $count = $this->admin->membersCount($survey);
        // then
        $this->assertSame(1, $count);
    }

    #[Test]
    public function countAcceptedUsers(): void
    {
        // given
        $survey = $this->newSurveyWithMembers([
            $this->newUserReturnId(state:'survey-accepted'),
            $this->newUserReturnId(state:'survey-declined'),
        ]);
        // when
        $invitedUsers = $this->admin->membersCountOfState($survey, 'survey-accepted');
        // then
        $this->assertSame(1, $invitedUsers);
    }

    #[Test]
    public function membersStatistics(): void
    {
        // given
        $survey = $this->newSurveyWithMembers([
            $this->newUserReturnId(state:'survey-accepted'),
            $this->newUserReturnId(state:'survey-accepted'),
            $this->newUserReturnId(state:'survey-accepted'),
            $this->newUserReturnId(state:'survey-declined'),
            $this->newUserReturnId(state:'survey-declined'),
            $this->newUserReturnId(state:'survey-invited'),
        ]);
        // when
        $statistic = $this->admin->membersStatistic($survey);
        // then
        $this->assertSame([
            'survey-accepted' => 3,
            'survey-declined' => 2,
            'survey-invited'  => 1,
        ],
            $statistic);
    }

    private function newSurveyWithMembers(array $memberIds): Survey
    {
        $survey = $this->admin->newSurvey('irrelevant');
        $this->admin->updateMembers($survey, $memberIds);
        return $survey;
    }

    private function newUserReturnId(string $state): int
    {
        $userId = $this->dsl->newUserReturnId();
        $this->setUserState($userId, $state);
        return $userId;
    }

    private function setUserState(int $userId, string $state): void
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);
        $survey = new GuestSurvey(new Guest($user->guest_id), new MemoryClock());
        $survey->setState($state);
    }
}
