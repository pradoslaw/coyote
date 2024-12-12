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
    public function __construct(
        private Dispatcher     $dispatcher,
        private UserRepository $user) {}

    public function handle(PostSaved $event): void
    {
        $post = $event->post;

        if ($event->wasRecentlyCreated) {
            $user = $post->user;
            $subscribers = $post->topic
                ->subscribers()
                ->excludeUserAndBlockers($user->id)
                ->has('user')
                ->with('user')
                ->get()
                ->pluck('user');
            $notification = new SubmittedNotification($user, $post);
            $subscribers = $subscribers
                ->merge($user->followers)
                ->unique('id');
            $this->dispatcher->send($subscribers, $notification);
            $this->sendUserMentionedNotification($post, $user, $subscribers);
        } else {
            if ($post->editor) {
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
        // get id of users that were mentioned in the text
        $usersId = (new Helper\Login)->grab($post->html);

        if ($post->user->reputation < Reputation::USER_MENTION) {
            return;
        }

        if (!empty($usersId)) {
            $this->dispatcher->send(
                $this->user->excludeUserAndBlockers($post->user_id)->findMany($usersId)->exceptUsers($subscribers),
                new UserMentionedNotification($user, $post));
        }
    }
}
