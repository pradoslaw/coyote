<?php
namespace Coyote\Listeners;

use Coyote\Events\PostSaved;
use Coyote\Notifications\Post\ChangedNotification;
use Coyote\Notifications\Post\SubmittedNotification;
use Coyote\Notifications\Post\UserMentionedNotification;
use Coyote\Post;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Reputation;
use Coyote\Services\Helper;
use Coyote\User;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support;

class DispatchPostNotifications implements ShouldQueue
{
    public function __construct(private Dispatcher $dispatcher, private UserRepository $users) {}

    public function handle(PostSaved $event): void
    {
        $this->handlePostSaved($event->post, $event->wasRecentlyCreated);
    }

    private function handlePostSaved(Post $post, bool $wasRecentlyCreated): void
    {
        if ($wasRecentlyCreated) {
            $this->handlePostCreated($post, $post->user);
        }
        if (!$wasRecentlyCreated && $post->editor) {
            $this->handlePostEdited($post, $post->editor);
        }
    }

    private function handlePostCreated(Post $post, User $author): void
    {
        $users = $this->postUsersToMention($post);
        $this->sendNotificationUserMentioned($post, $author, $users);
        $this->sendNotificationPostCreated($post, $author, $users);
    }

    private function handlePostEdited(Post $post, User $editor): void
    {
        $users = $this->postUsersToMention($post);
        $this->sendNotificationUserMentioned($post, $editor, $users);
        $this->sendNotificationPostEdited($post, $editor, $users);
    }

    private function postUsersToMention(Post $post): array
    {
        if ($post->user === null) {
            return [];
        }
        if ($post->user->reputation >= Reputation::USER_MENTION) {
            return (new Helper\Login)->grab($post->html);
        }
        return [];
    }

    private function sendNotificationPostCreated(Post $post, User $author, array $alreadyMentioned): void
    {
        $this->dispatcher->send(
            $this
                ->topicSubscribers($post, $author, $alreadyMentioned)
                ->merge($author->followers)
                ->unique('id'),
            new SubmittedNotification($author, $post));
    }

    private function sendNotificationPostEdited(Post $post, User $editor, array $alreadyMentioned): void
    {
        $this->dispatcher->send(
            $this->postSubscribers($post, $editor, $alreadyMentioned),
            new ChangedNotification($editor, $post));
    }

    private function sendNotificationUserMentioned(Post $post, User $notifier, array $mentionedUserIds): void
    {
        if (empty($mentionedUserIds)) {
            return;
        }
        $this->dispatcher->send(
            $this->users->excludeUserAndBlockers($post->user_id)->findMany($mentionedUserIds),
            new UserMentionedNotification($notifier, $post));
    }

    private function topicSubscribers(Post $post, User $notifiableBy, array $exceptSubscribers): Support\Collection
    {
        return $post->topic
            ->subscribers()
            ->whereNotIn('subscriptions.user_id', $exceptSubscribers)
            ->excludeUserAndBlockers($notifiableBy->id)
            ->has('user')
            ->with('user')
            ->get()
            ->pluck('user');
    }

    private function postSubscribers(Post $post, User $notifiableBy, array $exceptSubscribers): Support\Collection
    {
        return $post
            ->subscribers()
            ->whereNotIn('subscriptions.user_id', $exceptSubscribers)
            ->excludeUserAndBlockers($notifiableBy->id)
            ->has('user')
            ->with('user')
            ->get()
            ->pluck('user');
    }
}
