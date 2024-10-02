<?php
namespace Tests\Unit\OnlineUsers;

use Coyote\Domain\Online\SessionsSnapshot;
use Coyote\Domain\Online\ViewersStore;
use Coyote\Domain\Online\ViewerUser;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Forum\Models;

class ViewersStoreTest extends TestCase
{
    use Models;

    private ViewersStore $store;

    #[Before]
    public function initialize(): void
    {
        $this->store = new ViewersStore();
    }

    #[Test]
    public function remainsGuestsCount(): void
    {
        $viewer = $this->store->viewers($this->guests(99));
        $this->assertSame(99, $viewer->guestsCount);
    }

    #[Test]
    public function viewerHasUserName(): void
    {
        $id = $this->models->newUserReturnId('Mark');
        $this->assertSame('Mark', $this->viewer($id)->name);
    }

    #[Test]
    public function viewerHasUserGroupName(): void
    {
        $id = $this->models->newUserReturnId(groupName:'Admin');
        $this->assertSame('Admin', $this->viewer($id)->groupName);
    }

    #[Test]
    public function viewerHasUserAvatar(): void
    {
        $id = $this->models->newUserReturnId(photoUrl:'image.png');
        $this->assertSame('image.png', $this->viewer($id)->avatarUrl);
    }

    #[Test]
    public function forUserWithoutGroup_viewerHasGroupNameNull(): void
    {
        $id = $this->models->newUserReturnId();
        $this->assertNull($this->viewer($id)->groupName);
    }

    #[Test]
    public function deletedUsers_areNotIncluded(): void
    {
        $id = $this->models->newUserReturnId(deleted:true);
        $this->assertEmpty($this->viewers($id));
    }

    private function guests(int $guestsCount): SessionsSnapshot
    {
        return new SessionsSnapshot([], $guestsCount);
    }

    private function users(array $users): SessionsSnapshot
    {
        return new SessionsSnapshot($users, 0);
    }

    private function viewer(int $id): ViewerUser
    {
        return $this->viewers($id)[0];
    }

    private function viewers(int $id): array
    {
        $viewer = $this->store->viewers($this->users([$id]));
        return $viewer->users;
    }
}
