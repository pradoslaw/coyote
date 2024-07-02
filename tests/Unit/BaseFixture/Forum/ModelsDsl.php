<?php
namespace Tests\Unit\BaseFixture\Forum;

readonly class ModelsDsl
{
    private ModelsFactory $models;

    public function __construct()
    {
        $this->models = new ModelsFactory();
    }

    public function newUser(string $name = null): void
    {
        $this->models->newUserReturn(name:$name);
    }

    public function newUserDeleted(string $name): void
    {
        $this->models->newUserReturn(name:$name, deleted:true);
    }

    public function newUserReturnId(string $permissionName = null): int
    {
        $user = $this->models->newUserReturn();
        if ($permissionName) {
            $this->models->assignToGroupWithPermission($user, $permissionName);
        }
        return $user->id;
    }

    public function newUserConfirmedEmail(string $email): void
    {
        $this->models->newUserReturn(email:$email, emailConfirmed:true);
    }

    public function newMicroblog(string $contentMarkdown = null): void
    {
        $this->models->newMicroblogReturnId($contentMarkdown);
    }

    public function newMicroblogReturnId(): int
    {
        return $this->models->newMicroblogReturnId(null);
    }

    public function newPostReported(string $reportContent = null): void
    {
        $post = $this->models->newPostReturn();
        $this->models->reportPost($post, $reportContent);
    }

    public function newPostDeletedReported(string $reportContent): void
    {
        $post = $this->models->newPostReturn();
        $post->delete();
        $this->models->reportPost($post, $reportContent);
    }
}
