<?php
namespace Coyote\Listeners;

use Coyote\Events\UserDeleted;
use Coyote\Events\UserSaved;
use Coyote\Feature\DraftPost\DraftPostService;
use Coyote\Services\Elasticsearch\Crawler;
use Coyote\Services\PostService;
use Coyote\User;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Connection;
use Illuminate\Events\Dispatcher;

class UserSubscriber
{
    public function __construct(
        private Repository       $cache,
        private Connection       $connection,
        private PostService      $postService,
        private DraftPostService $draftPost,
    ) {}

    public function flushCache(UserSaved $event)
    {
        $this->cache->tags('permissions')->forget('permission:' . $event->user->id);
        $this->cache->tags('forum-order')->forget('forum-order:' . $event->user->id);
    }

    public function index(UserSaved $event)
    {
        $user = $event->user;

        dispatch_sync(function () use ($user) {
            (new Crawler())->index($user);
        });
    }

    public function delete(UserDeleted $event)
    {
        $user = (new User())->forceFill($event->user);

        dispatch_sync(function () use ($user) {
            (new Crawler())->delete($user);
        });
    }

    public function convertDrafts(UserSaved $event): void
    {
        $this->connection->transaction(fn() => $this->convertAndRemoveDrafts($event->user->guest_id, $event->user));
    }

    private function convertAndRemoveDrafts(string $guestId, User $user): void
    {
        foreach ($this->draftPost->fetchDrafts($guestId) as [$topicId, $text]) {
            $this->postService->createPost(
                $topicId,
                $text,
                $user->id,
                request()->ip(),
                str_limit(request()->browser(), 250));
        }
        $this->draftPost->removeDrafts($guestId);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(UserSaved::class, 'Coyote\Listeners\UserSubscriber@flushCache');
        $events->listen(UserSaved::class, 'Coyote\Listeners\UserSubscriber@index');
        $events->listen(UserSaved::class, 'Coyote\Listeners\UserSubscriber@convertDrafts');
        $events->listen(UserDeleted::class, 'Coyote\Listeners\UserSubscriber@delete');
    }
}
