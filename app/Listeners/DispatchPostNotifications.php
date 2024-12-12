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
            $user = $post->user;
            $subscribers = $post->topic
                ->subscribers()
                ->excludeUserAndBlockers($user->id)
                ->has('user')
                ->with('user')
                ->get()
                ->pluck('user');
            $subscribers = $subscribers
                ->merge($user->followers)
                ->unique('id');
            $this->dispatcher->send($subscribers, new SubmittedNotification($user, $post));
            $this->sendUserMentionedNotification($post, $user, $subscribers);
        }
        if (!$wasRecentlyCreated && $post->editor) {
            $user = $post->editor;
            $subscribers = $post
                ->subscribers()
                ->excludeUserAndBlockers($user->id)
                ->has('user')
                ->with('user')
                ->get()
                ->pluck('user');
            $this->dispatcher->send($subscribers, new ChangedNotification($user, $post));
            $this->sendUserMentionedNotification($post, $user, $subscribers);
        }
    }

    private function sendUserMentionedNotification(
        Post               $post,
        User               $user,
        Support\Collection $subscribers,
    ): void
    {
        if ($post->user === null) {
            return;
        }

        if ($post->user->reputation < Reputation::USER_MENTION) {
            return;
        }

        $usersId = $this->mentionedUserIds($post);
        if (!empty($usersId)) {
            $this->dispatcher->send(
                $this->users->excludeUserAndBlockers($post->user_id)->findMany($usersId)->exceptUsers($subscribers),
                new UserMentionedNotification($user, $post));
        }
    }

    private function mentionedUserIds(Post $post): array
    {
        return (new Helper\Login)->grab($post->html);
    }
}
