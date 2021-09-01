<?php

namespace Coyote\Listeners;

use Coyote\Events\PostSaved;
use Coyote\Notifications\Post\ChangedNotification;
use Coyote\Notifications\Post\SubmittedNotification;
use Coyote\Notifications\Post\UserMentionedNotification;
use Coyote\Post;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Parser\Helpers\Login as LoginHelper;
use Coyote\User;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchPostNotifications implements ShouldQueue
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @param Dispatcher $dispatcher
     * @param UserRepository $user
     */
    public function __construct(Dispatcher $dispatcher, UserRepository $user)
    {
        $this->dispatcher = $dispatcher;
        $this->user = $user;
    }

    /**
     * Handle the event.
     *
     * @param  PostSaved  $event
     * @return void
     */
    public function handle(PostSaved $event)
    {
        $post = $event->post;
        $topic = $event->post->topic;

        if ($event->wasRecentlyCreated) {
            $user = $event->post->user;

            $subscribers = $topic->subscribers()->with('user:id,name')->get()->pluck('user')->exceptUser($user);
            $notification = (new SubmittedNotification($user, $post))->setSender($this->getSender($post));

            $subscribers = $subscribers
                ->merge($user->followers)
                ->unique('id');

            $this->dispatcher->send($subscribers, $notification);

            $this->sendUserMentionedNotification($post, $user, $subscribers, $this->getSender($post));
        } elseif ($event->post->editor) {
            $user = $event->post->editor;
            $subscribers = $post->subscribers()->with('user:id,name')->get()->pluck('user')->exceptUser($user);

            $this->dispatcher->send($subscribers, new ChangedNotification($user, $post));

            $this->sendUserMentionedNotification($post, $user, $subscribers, $user->name);
        }
    }

    /**
     * @param Post $post
     * @param User|null $user
     * @param array $subscribers
     * @param string $senderName
     */
    private function sendUserMentionedNotification(Post $post, ?User $user, $subscribers, string $senderName): void
    {
        // get id of users that were mentioned in the text
        $usersId = (new LoginHelper())->grab($post->html);

        if (!empty($usersId)) {
            $this->dispatcher->send(
                $this->user->findMany($usersId)->exceptUser($user)->exceptUsers($subscribers),
                (new UserMentionedNotification($user, $post))->setSender($senderName)
            );
        }
    }

    /**
     * @param Post $post
     * @return string
     */
    private function getSender(Post $post): string
    {
        return $post->user ? $post->user->name : $post->user_name;
    }
}
