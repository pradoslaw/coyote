<?php

namespace Tests\Feature\Listeners;

use Coyote\Events\PostSaved;
use Coyote\Forum;
use Coyote\Notifications\Post\SubmittedNotification;
use Coyote\Notifications\Post\UserMentionedNotification;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DispatchPostNotificationsTest extends TestCase
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

    public function testDispatchMentionNotification()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $post = factory(Post::class)->state('user')->create(['text' => "Hello @{{$user->name}}", 'topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);

        Notification::fake();

        event(new PostSaved($post));

        Notification::assertSentTo($user, UserMentionedNotification::class);
    }

    public function testDispatchNotificationToFollowers()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $follower = factory(User::class)->create();

        $follower->relations()->create(['related_user_id' => $user->id, 'is_blocked' => false]);

        $post = factory(Post::class)->create(['user_id' => $user->id, 'topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);

        Notification::fake();

        event(new PostSaved($post));

        Notification::assertSentTo($follower, SubmittedNotification::class);
    }
//
//    public function testDispatchOnlyOneNotification()
//    {
//        /** @var Microblog $microblog */
//        $microblog = factory(Microblog::class)->create();
//        $microblog->subscribers()->create(['user_id' => $microblog->user_id]);
//
//        $comment = factory(Microblog::class)->create(['parent_id' => $microblog->id, 'text' => "Hello @{$microblog->user->name}"]);
//        $comment->wasRecentlyCreated = true;
//
//        Notification::fake();
//
//        event(new MicroblogSaved($comment));
//
//        Notification::assertNotSentTo($microblog->user, UserMentionedNotification::class);
//    }
}
