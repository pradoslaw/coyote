<?php

namespace Coyote\Listeners;

use Coyote\Events\CommentSaved;
use Coyote\Notifications\Post\Comment\UserMentionedNotification;
use Coyote\Notifications\Post\CommentedNotification;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Reputation;
use Coyote\Services\Parser\Helpers\Login as LoginHelper;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchPostCommentNotification implements ShouldQueue
{
    /**
     * @param Dispatcher $dispatcher
     * @param UserRepository $user
     */
    public function __construct(private Dispatcher $dispatcher)
    {
    }

    public function handle(CommentSaved $event)
    {
        $comment = $event->comment;
        /** @var \Coyote\User[]|\Illuminate\Support\Collection $subscribers */
        $subscribers = [];

        if ($event->wasRecentlyCreated) {
            $subscribers = $comment->post->subscribers()->with('user')->get()->pluck('user')->exceptUser($comment->user);

            $this->dispatcher->send(
                $subscribers,
                (new CommentedNotification($comment))
            );
        }

        $usersId = (new LoginHelper())->grab($comment->html);

        if ($comment->user->reputation < Reputation::USER_MENTION) {
            return;
        }

        if (!empty($usersId)) {
            $this->dispatcher->send(
                app(UserRepositoryInterface::class)->findMany($usersId)->exceptUser($comment->user)->exceptUsers($subscribers),
                new UserMentionedNotification($comment)
            );
        }
    }
}
