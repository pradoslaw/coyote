<?php
namespace Tests\Unit\BaseFixture\Forum;

use Coyote\Microblog;
use Coyote\User;

class ModelsFactory
{
    public function newUser(string $name = null): void
    {
        $this->newUserReturn(name:$name);
    }

    public function newUserDeleted(string $name): void
    {
        $this->newUserReturn(name:$name, deleted:true);
    }

    public function newUserConfirmedEmail(string $email): void
    {
        $this->newUserReturn(email:$email, emailConfirmed:true);
    }

    private function newUserReturn(
        string $name = null,
        string $email = null,
        bool   $emailConfirmed = null,
        bool   $deleted = null,
    ): User
    {
        $user = new User();
        $user->name = $name ?? 'irrelevant' . \uniqId();
        $user->email = $email ?? 'irrelevant';
        $user->is_confirm = $emailConfirmed ?? false;
        $user->deleted_at = $deleted;
        $user->save();
        return $user;
    }

    public function newMicroblog(string $contentMarkdown = null): void
    {
        $this->createMicroblog($contentMarkdown);
    }

    public function newMicroblogReturnId(): int
    {
        return $this->createMicroblog(null);
    }

    private function createMicroblog(?string $contentMarkdown): int
    {
        /** @var Microblog $microblog */
        $microblog = Microblog::query()->create([
            'user_id' => $this->newUserReturn()->id,
            'text'    => $contentMarkdown ?? 'irrelevant',
        ]);
        return $microblog->id;
    }
}
