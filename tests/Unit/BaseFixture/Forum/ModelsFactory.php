<?php
namespace Tests\Unit\BaseFixture\Forum;

use Coyote\Microblog;
use Coyote\User;

class ModelsFactory
{
    public function newUser(): void
    {
        $this->newUserReturn();
    }

    private function newUserReturn(): User
    {
        $user = new User();
        $user->name = 'irrelevant' . \uniqId();
        $user->email = 'irrelevant';
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
