<?php

namespace Coyote\Listeners;

use Coyote\Events\CommentSaved;
use Coyote\Notifications\Post\Comment\UserMentionedNotification;
use Coyote\Notifications\Post\CommentedNotification;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Parser\Helpers\Login as LoginHelper;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchPostCommentNotification implements ShouldQueue
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

    public function handle(CommentSaved $event)
    {
        $comment = $event->comment;
        /** @var \Coyote\User[]|\Illuminate\Support\Collection $subscribers */
        $subscribers = [];

        if ($event->wasRecentlyCreated) {
            $subscribers = $comment->post->subscribers()->with('user:id,name')->get()->pluck('user')->exceptUser($comment->user);

            $this->dispatcher->send(
                $subscribers,
                (new CommentedNotification($comment))
            );
        }

        $usersId = (new LoginHelper())->grab($comment->html);

        if (!empty($usersId)) {
            $this->dispatcher->send(
                app(UserRepositoryInterface::class)->findMany($usersId)->exceptUser($comment->user)->exceptUsers($subscribers),
                new UserMentionedNotification($comment)
            );
        }
    }
}
