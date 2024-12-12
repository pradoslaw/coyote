<?php
namespace Tests\Legacy\Listeners;

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
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\TestCase;

class DispatchPostNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    private Forum $forum;
    private Topic $topic;

    #[PreCondition]
    public function initialize(): void
    {
        $this->forum = factory(Forum::class)->create();
        $this->topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        Notification::fake();
    }

    #[Test]
    public function testDispatchNotificationToSubscribers()
    {
        $user = $this->newUser();
        $this->userSubscribesTopic($user, $this->topic);
        [$post, $postAuthor] = $this->someoneWritesPost();

        event(new PostSaved($post));

        Notification::assertSentTo($user, function (SubmittedNotification $notification, array $channels) use ($postAuthor) {
            $this->assertContains(DatabaseChannel::class, $channels);
            $this->assertContains(WebPushChannel::class, $channels);
            $this->assertContains('mail', $channels);
            $this->assertContains('broadcast', $channels);

            return $notification->notifier->id === $postAuthor->id;
        });
    }

    #[Test]
    public function testDoNotDispatchNotificationToMySelf()
    {
        $user = $this->newUser();
        $this->userSubscribesTopic($user, $this->topic);
        $post = $this->userAddsPost($user);

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function testDoNotDispatchNotificationDueToUserWasBlocked()
    {
        $user = $this->newUser();
        $this->userSubscribesTopic($user, $this->topic);
        $blocked = $this->newUser();
        $this->userBlocksUser($user, $blocked);
        $post = $this->userAddsPost($blocked);

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function testDispatchMentionNotification()
    {
        $authorUser = $this->newUser(canMentionUsers:true);
        $mentionedUser = $this->newUser();
        $post = $this->userAddsPost($authorUser, "Hello @{{$mentionedUser->name}}");

        event(new PostSaved($post));

        Notification::assertSentTo($mentionedUser, UserMentionedNotification::class);
    }

    #[Test]
    public function testDoNotDispatchMentionNotificationToMyself()
    {
        $user = $this->newUser();
        $post = $this->userAddsPost($user, "Hello @{{$user->name}}");

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function testDoNotDispatchMentionNotificationDueToUserWasBlocked()
    {
        $user = $this->newUser();
        $blocked = $this->newUser();
        $this->userBlocksUser($user, $blocked);
        $post = $this->userAddsPost($blocked, "Hello @{{$user->name}}");

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function testDispatchNotificationToFollowers()
    {
        $user = $this->newUser();
        $follower = $this->newUser();
        $this->userFollowsUser($follower, $user);
        $post = $this->userAddsPost($user);

        event(new PostSaved($post));

        Notification::assertSentTo($follower, SubmittedNotification::class);
    }

    #[Test]
    public function testDispatchOnlyOneNotification()
    {
        $followee = $this->newUser();
        $user = $this->newUser();
        $this->userSubscribesTopic($followee, $this->topic);
        $this->userSubscribesTopic($user, $this->topic);
        $this->userFollowsUser($user, $followee);
        $post = $this->userAddsPost($followee);

        event(new PostSaved($post));

        Notification::assertSentToTimes($user, SubmittedNotification::class, 1);
    }

    #[Test]
    public function testDispatchChangedNotification()
    {
        $user = $this->newUser();
        [$post, $postAuthor] = $this->someoneWritesPost();

        $this->userSubscribesPost($user, $post);

        $post->editor_id = $postAuthor->id;
        $post->wasRecentlyCreated = false;
        $post->save();

        event(new PostSaved($post));

        Notification::assertSentTo($user, ChangedNotification::class);
    }

    #[Test]
    public function testDoNotDispatchChangedNotificationDueUserWasBlocked()
    {
        $blocked = $this->newUser();
        [$post, $postAuthor] = $this->someoneWritesPost();
        $this->userSubscribesPost($blocked, $post);
        $this->userBlocksUser($postAuthor, $blocked);

        $post->editor_id = $blocked->id;
        $post->wasRecentlyCreated = false;
        $post->save();

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    private function newUser(bool $canMentionUsers = false): User
    {
        $factoryBuilder = factory(User::class);
        if ($canMentionUsers) {
            $factoryBuilder->state('canMentionUsers');
        }
        return $factoryBuilder->create();
    }

    private function userSubscribesTopic(User $user, Topic $topic): void
    {
        $topic->subscribers()->create(['user_id' => $user->id]);
    }

    private function userSubscribesPost(User $user, Post $post): void
    {
        $post->subscribers()->create(['user_id' => $user->id]);
    }

    private function userFollowsUser(User $user, User $followee): void
    {
        $user->relations()->create(['related_user_id' => $followee->id, 'is_blocked' => false]);
    }

    private function userBlocksUser(User $user, User $blocked): void
    {
        $user->relations()->create(['related_user_id' => $blocked->id, 'is_blocked' => true]);
    }

    private function userAddsPost(User $user, string $postMarkdown = null): Post
    {
        $attributes = [
            'user_id'  => $user->id,
            'forum_id' => $this->forum->id,
            'topic_id' => $this->topic->id,
        ];
        if ($postMarkdown) {
            $attributes['text'] = $postMarkdown;
        }
        return factory(Post::class)->create($attributes);
    }

    private function someoneWritesPost(): array
    {
        /** @var Post $post */
        $post = factory(Post::class)->create([
            'topic_id' => $this->topic->id,
            'forum_id' => $this->forum->id,
        ]);
        return [$post, $post->user];
    }
}
