<?php
namespace Tests\Unit\BaseFixture\Forum;

use Coyote\Flag;
use Coyote\Forum;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\Topic;
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

    public function newUserReturnId(): int
    {
        return $this->newUserReturn()->id;
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

    public function newPostReported(string $reportContent = null): void
    {
        $this->createPostWithReport($reportContent, null);
    }

    public function newPostDeletedReported(string $reportContent): void
    {
        $this->createPostWithReport($reportContent, 1);
    }

    private function createPostWithReport(?string $reportContent, ?int $deletedAt): void
    {
        $forumId = $this->newForumReturnId();
        /** @var Post $post */
        $post = Post::query()->create([
            'text'     => 'irrelevant',
            'ip'       => 'irrelevant',
            'topic_id' => $this->newTopicReturnId($forumId),
            'forum_id' => $forumId,
        ]);
        if ($deletedAt) {
            $post->delete();
        }
        $flag = $this->newReport($reportContent);
        $flag->posts()->attach($post->id);
        $flag->save();
    }

    private function newReport(?string $content): Flag
    {
        /** @var Flag $flag */
        $flag = Flag::query()->create([
            'type_id' => $this->flagTypeId(),
            'user_id' => $this->newUserReturnId(),
            'url'     => '',
            'text'    => $content,
        ]);
        return $flag;
    }

    private function flagTypeId(): int
    {
        return Flag\Type::query()->first()->id;
    }

    private function newTopicReturnId(int $forumId): int
    {
        /** @var Topic $model */
        $model = Topic::query()->create(['title' => 'irrelevant', 'forum_id' => $forumId]);
        return $model->id;
    }

    private function newForumReturnId(): int
    {
        /** @var Forum $forum */
        $forum = Forum::query()->create(['name' => 'irrelevant', 'slug' => 'irrelevant', 'description' => 'irrelevant']);
        return $forum->id;
    }
}
