<?php

namespace Tests\Feature\Listeners;

use Coyote\Events\CommentSaved;
use Coyote\Forum;
use Coyote\Notifications\Post\CommentedNotification;
use Coyote\Post\Comment;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DispatchPostCommentNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Forum $forum;
    private Topic $topic;

    public function setUp(): void
    {
        parent::setUp();

        $this->forum = factory(Forum::class)->create();
        $this->topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
    }

    public function testDispatchNotificationToFollowers()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $follower = factory(User::class)->create();

        $this->topic->refresh();

        $follower->relations()->create(['related_user_id' => $user->id, 'is_blocked' => false]);
        $comment = factory(Comment::class)->create(['user_id' => $user->id, 'post_id' => $this->topic->first_post_id]);

        Notification::fake();

        event(new CommentSaved($comment));

        Notification::assertSentTo($follower, CommentedNotification::class);
    }
}
