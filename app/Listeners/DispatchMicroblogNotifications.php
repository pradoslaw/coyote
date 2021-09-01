<?php

namespace Coyote\Listeners;

use Coyote\Events\MicroblogSaved;
use Coyote\Microblog;
use Coyote\Notifications\Microblog\CommentedNotification;
use Coyote\Notifications\Microblog\SubmittedNotification;
use Coyote\Notifications\Microblog\UserMentionedNotification;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Parser\Helpers\Login as LoginHelper;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchMicroblogNotifications implements ShouldQueue
{
    /**
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;

    /**
     * @var UserRepository
     */
    private UserRepository $user;

    /**
     * @param Dispatcher $dispatcher
     * @param UserRepository $user
     */
    public function __construct(Dispatcher $dispatcher, UserRepository $user)
    {
        $this->dispatcher = $dispatcher;
        $this->user = $user;
    }

    public function handle(MicroblogSaved $event)
    {
        // microblog could be deleted at this point
        if ($this->shouldNotSendNotification($event)) {
            return false;
        }

        $microblog = $event->microblog;
        /** @var \Coyote\User[]|\Illuminate\Support\Collection  $subscribers */
        $subscribers = [];

        if ($event->wasRecentlyCreated) {
            if ($microblog->parent_id) {
                $subscribers = $microblog
                    ->parent
                    ->subscribers()
                    // exclude also author of parent entry! @see https://github.com/adam-boduch/coyote/issues/637
                    ->excludeBlocked($microblog->user_id, $microblog->parent->user_id)
                    ->has('user') // <-- make sure to skip records with deleted users
                    ->with(['user:id,name'])
                    ->get()
                    ->pluck('user');
            } else {
                $subscribers = $microblog->user->followers;
            }

            if (count($subscribers->filter())) {
                $this->dispatcher->send(
                    $subscribers,
                    $microblog->parent_id ? new CommentedNotification($microblog) : new SubmittedNotification($microblog)
                );
            }
        }

        if ($event->wasContentChanged) {
            $this->sendUserMentionedNotifications($microblog, $subscribers);
        }

        return true;
    }

    private function shouldNotSendNotification(MicroblogSaved $event): bool
    {
        return !$event->microblog || (!$event->wasRecentlyCreated && !$event->wasContentChanged);
    }

    private function sendUserMentionedNotifications(Microblog $microblog, $subscribers)
    {
        $helper = new LoginHelper();
        // get id of users that were mentioned in the text
        $usersId = $helper->grab($microblog->html);

        if (!empty($usersId)) {
            $this->dispatcher->send(
                $this->user->excludeBlocked($microblog->user->id)->findMany($usersId)->exceptUsers($subscribers),
                new UserMentionedNotification($microblog)
            );
        }
    }
}
