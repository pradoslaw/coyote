<?php

namespace Coyote\Listeners;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\PostWasDeleted;
use Coyote\Events\TopicWasDeleted;
use Coyote\Flag;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;

class FlagSubscriber implements ShouldQueue
{
    public function handleTopicDelete(TopicWasDeleted $event)
    {
        Flag::whereHas('topics', fn (Builder $query) => $query->withTrashed()->where('id', $event->topic['id']))->delete();
    }

    public function handlePostDelete(PostWasDeleted $event)
    {
        Flag::whereHas('posts', fn (Builder $query) => $query->withTrashed()->where('id', $event->post['id']))->delete();
    }

    public function handleMicroblogDelete(MicroblogWasDeleted $event)
    {
        Flag::whereHas('microblogs', fn (Builder $query) => $query->withTrashed()->where('id', $event->microblog['id']))->delete();
    }

    public function subscribe($events)
    {
        $events->listen(
            TopicWasDeleted::class,
            'Coyote\Listeners\FlagSubscriber@handleTopicDelete'
        );

        $events->listen(
            PostWasDeleted::class,
            'Coyote\Listeners\FlagSubscriber@handlePostDelete'
        );

        $events->listen(
            MicroblogWasDeleted::class,
            'Coyote\Listeners\FlagSubscriber@handleMicroblogDelete'
        );
    }
}
