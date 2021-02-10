<?php

namespace Tests\Feature\Listeners;

use Coyote\Events\CommentSaved;
use Coyote\Forum;
use Coyote\Notifications\Post\CommentedNotification;
use Coyote\Notifications\Post\Comment\UserMentionedNotification;
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

        Notification::fake();
    }

    public function testDispatchNotificationToSubscribers()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $subscriber = factory(User::class)->create();

        $this->topic->firstPost->subscribe($subscriber->id, true);
        $this->topic->refresh();

        $comment = factory(Comment::class)->create(['user_id' => $user->id, 'post_id' => $this->topic->first_post_id]);

        event(new CommentSaved($comment));

        Notification::assertSentTo($subscriber, CommentedNotification::class);
    }

    public function testDispatchMentionNotification()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $subscriber = factory(User::class)->create();

        $comment = factory(Comment::class)->create(['user_id' => $user->id, 'post_id' => $this->topic->firstPost->id, 'text' => "Hello @{{$subscriber->name}}"]);

        event(new CommentSaved($comment));

        Notification::assertSentTo($subscriber, UserMentionedNotification::class);
    }

//    public function testDispatchNotificationToFollowers()
//    {
//        /** @var User $user */
//        $user = factory(User::class)->create();
//        $follower = factory(User::class)->create();
//
//        $this->topic->refresh();
//
//        $follower->relations()->create(['related_user_id' => $user->id, 'is_blocked' => false]);
//        $comment = factory(Comment::class)->create(['user_id' => $user->id, 'post_id' => $this->topic->first_post_id]);
//
//        Notification::fake();
//
//        event(new CommentSaved($comment));
//
//        Notification::assertSentTo($follower, CommentedNotification::class);
//    }
}
