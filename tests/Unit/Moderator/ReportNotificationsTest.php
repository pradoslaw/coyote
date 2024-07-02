<?php
namespace Tests\Unit\Moderator;

use Coyote\Domain\Moderator\ReportNotifications;
use Coyote\Flag;
use Coyote\User;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class ReportNotificationsTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use BaseFixture\Forum\Models;

    #[Before]
    public function removeFlags(): void
    {
        Flag::query()->delete();
    }

    #[Test]
    public function anonymousUserShouldNotSeeReports(): void
    {
        $userId = $this->models->newUserReturnId();
        $this->assertHasReportNotification(false, $userId);
    }

    #[Test]
    public function userWithAdministratorAccessCanSeeReports(): void
    {
        $userId = $this->models->newUserReturnId('adm-access');
        $this->assertHasReportNotification(true, $userId);
    }

    private function assertHasReportNotification(bool $expected, int $userId): void
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);
        $reportNotifications = new ReportNotifications($user);
        $this->assertSame($expected, $reportNotifications->hasAccess());
    }

    #[Test]
    public function guestShouldNotSeeReports(): void
    {
        $reportNotifications = new ReportNotifications(user:null);
        $this->assertFalse($reportNotifications->hasAccess());
    }

    #[Test]
    public function reportsHasAnyEmpty(): void
    {
        $reportNotifications = new ReportNotifications(new User());
        $this->assertFalse($reportNotifications->hasAny());
    }

    #[Test]
    public function reportsHasAny(): void
    {
        $this->models->newPostReported('');
        $reportNotifications = new ReportNotifications(new User());
        $this->assertTrue($reportNotifications->hasAny());
    }

    #[Test]
    public function reportsCountEmpty(): void
    {
        $reportNotifications = new ReportNotifications(new User());
        $this->assertSame(0, $reportNotifications->count());
    }

    #[Test]
    public function reportsCount(): void
    {
        $this->models->newPostReported('');
        $reportNotifications = new ReportNotifications(new User());
        $this->assertSame(1, $reportNotifications->count());
    }

    #[Test]
    public function reportsCountMany(): void
    {
        $this->models->newPostReported('');
        $this->models->newPostReported('');
        $reportNotifications = new ReportNotifications(new User());
        $this->assertSame(2, $reportNotifications->count());
    }
}
