<?php

namespace Tests\Feature\Listeners;

use Coyote\Events\PostSaved;
use Coyote\Forum;
use Coyote\Notifications\Post\ChangedNotification;
use Coyote\Notifications\Post\SubmittedNotification;
use Coyote\Notifications\Post\UserMentionedNotification;
use Coyote\Post;
use Coyote\Services\Notification\DatabaseChannel;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\WebPush\WebPushChannel;
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

        Notification::fake();
    }

    public function testDispatchNotificationToSubscribers()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $this->topic->subscribers()->create(['user_id' => $user->id]);

        $post = factory(Post::class)->state('user')->create(['topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);

        event(new PostSaved($post));

        Notification::assertSentTo($user, function (SubmittedNotification $notification, $channels) use ($post) {
            $this->assertContains(DatabaseChannel::class, $channels);
            $this->assertContains(WebPushChannel::class, $channels);
            $this->assertContains('mail', $channels);
            $this->assertContains('broadcast', $channels);

            return $notification->notifier->id === $post->user_id;
        });
    }

    public function testDoNotDispatchNotificationToMySelf()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $this->topic->subscribers()->create(['user_id' => $user->id]);

        $post = factory(Post::class)->create(['user_id' => $user->id, 'topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    public function testDoNotDispatchNotificationDueToUserWasBlocked()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        // watch topic
        $this->topic->subscribers()->create(['user_id' => $user->id]);

        /** @var User $blocked */
        $blocked = factory(User::class)->create();

        // ban user
        $user->relations()->create(['related_user_id' => $blocked->id, 'is_blocked' => true]);

        $post = factory(Post::class)->create(['user_id' => $blocked->id, 'topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);
        $post->wasRecentlyCreated = true;

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    public function testDispatchMentionNotification()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $post = factory(Post::class)->state('user')->create(['text' => "Hello @{{$user->name}}", 'topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);

        event(new PostSaved($post));

        Notification::assertSentTo($user, UserMentionedNotification::class);
    }

    public function testDoNotDispatchMentionNotificationToMyself()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create(['user_id' => $user->id, 'text' => "Hello @{{$user->name}}", 'topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    public function testDoNotDispatchMentionNotificationDueToUserWasBlocked()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var User $blocked */
        $blocked = factory(User::class)->create();

        // ban user
        $user->relations()->create(['related_user_id' => $blocked->id, 'is_blocked' => true]);

        $post = factory(Post::class)->create(['user_id' => $blocked->id, 'text' => "Hello @{{$user->name}}", 'topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);
        $post->wasRecentlyCreated = true;

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    public function testDispatchNotificationToFollowers()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $follower = factory(User::class)->create();

        $follower->relations()->create(['related_user_id' => $user->id, 'is_blocked' => false]);

        $post = factory(Post::class)->create(['user_id' => $user->id, 'topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);

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

        event(new PostSaved($post));

        Notification::assertSentToTimes($follower, SubmittedNotification::class, 1);
    }

    public function testDispatchChangedNotification()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Post $post */
        $post = factory(Post::class)->state('user')->create(['topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);
        $post->subscribers()->create(['user_id' => $user->id]);

        $post->editor_id = $post->user_id;
        $post->wasRecentlyCreated = false;
        $post->save();

        $this->assertEquals($post->user_id, $post->editor_id);

        event(new PostSaved($post));

        Notification::assertSentTo($user, ChangedNotification::class);
    }

    public function testDoNotDispatchChangedNotificationDueUserWasBlocked()
    {
        /** @var User $blocked */
        $blocked = factory(User::class)->create();

        /** @var Post $post */
        $post = factory(Post::class)->state('user')->create(['topic_id' => $this->topic->id, 'forum_id' => $this->forum->id]);
        $post->subscribers()->create(['user_id' => $blocked->id]);

        // post author added some user to blacklist
        $post->user->relations()->create(['related_user_id' => $blocked->id, 'is_blocked' => true]);

        $post->editor_id = $blocked->id;
        $post->wasRecentlyCreated = false;
        $post->save();

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }
}
