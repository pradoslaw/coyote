<?php

namespace Coyote\Listeners;

use Coyote\Events\MicroblogSaved;
use Coyote\Microblog;
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
        if (!$event->wasRecentlyCreated && !$event->wasContentChanged) {
            return false;
        }

        $microblog = $event->microblog;
        $subscribers = [];

        if ($event->wasRecentlyCreated && $microblog->parent_id) {
            $subscribers = $microblog->parent
                ->subscribers()
                ->excludeBlocked($microblog->user->id)
                ->with('user.notificationSettings')
                ->get()
                ->pluck('user');

            $this->dispatcher->send($subscribers, new SubmittedNotification($microblog));
        }

        if ($event->wasContentChanged) {
            $this->sendUserMentionedNotifications($event->microblog, $subscribers);
        }

        return true;
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