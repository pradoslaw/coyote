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
    public function newUserReturn(
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

    public function newPostReturn(): Post
    {
        $forumId = $this->newForumReturnId();
        /** @var Post $post */
        $post = Post::query()->create([
            'text'     => 'irrelevant',
            'ip'       => 'irrelevant',
            'topic_id' => $this->newTopicReturnId($forumId),
            'forum_id' => $forumId,
        ]);
        return $post;
    }

    private function newForumReturnId(): int
    {
        /** @var Forum $forum */
        $forum = Forum::query()->create(['name' => 'irrelevant', 'slug' => 'irrelevant', 'description' => 'irrelevant']);
        return $forum->id;
    }

    private function newTopicReturnId(int $forumId): int
    {
        /** @var Topic $model */
        $model = Topic::query()->create(['title' => 'irrelevant', 'forum_id' => $forumId]);
        return $model->id;
    }

    public function newMicroblogReturnId(?string $contentMarkdown): int
    {
        /** @var Microblog $microblog */
        $microblog = Microblog::query()->create([
            'user_id' => $this->newUserReturn()->id,
            'text'    => $contentMarkdown ?? 'irrelevant',
        ]);
        return $microblog->id;
    }

    public function reportPost(Post $post, ?string $reportContent): void
    {
        $flag = $this->newReport($reportContent);
        $flag->posts()->attach($post->id);
        $flag->save();
    }

    private function newReport(?string $content): Flag
    {
        /** @var Flag $flag */
        $flag = Flag::query()->create([
            'type_id' => $this->flagTypeId(),
            'user_id' => $this->newUserReturn()->id,
            'url'     => '',
            'text'    => $content,
        ]);
        return $flag;
    }

    private function flagTypeId(): int
    {
        return Flag\Type::query()->first()->id;
    }
}
