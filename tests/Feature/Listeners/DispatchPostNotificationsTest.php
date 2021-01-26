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

    public function testDispatchNotificationToSubscribers()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $this->topic->subscribers()->create(['user_id' => $user->id]);

        $post = factory(Post::class)->state('user')->create(['topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);

        Notification::fake();

        event(new PostSaved($post));

        Notification::assertSentTo($user, SubmittedNotification::class);
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

    public function testDispatchOnlyOneNotification()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $follower = factory(User::class)->create();

        $this->topic->subscribers()->create(['user_id' => $user->id]);
        $this->topic->subscribers()->create(['user_id' => $follower->id]);

        $follower->relations()->create(['related_user_id' => $user->id, 'is_blocked' => false]);

        $post = factory(Post::class)->create(['user_id' => $user->id, 'topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);

        Notification::fake();

        event(new PostSaved($post));

        Notification::assertSentToTimes($follower, SubmittedNotification::class, 1);
    }
}
