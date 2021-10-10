<?php

namespace Coyote\Listeners;

use Coyote\Events\MicroblogDeleted;
use Coyote\Events\PostWasDeleted;
use Coyote\Events\TopicDeleted;
use Coyote\Flag;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;

class FlagSubscriber implements ShouldQueue
{
    public function handleTopicDelete(TopicDeleted $event)
    {
        Flag::whereHas('topics', fn (Builder $query) => $query->withTrashed()->where('id', $event->topic['id']))->delete();
    }

    public function handlePostDelete(PostWasDeleted $event)
    {
        Flag::whereHas('posts', fn (Builder $query) => $query->withTrashed()->where('id', $event->post['id']))->delete();
    }

    public function handleMicroblogDelete(MicroblogDeleted $event)
    {
        Flag::whereHas('microblogs', fn (Builder $query) => $query->withTrashed()->where('id', $event->microblog['id']))->delete();
    }

    public function subscribe($events)
    {
        $events->listen(
            TopicDeleted::class,
            'Coyote\Listeners\FlagSubscriber@handleTopicDelete'
        );

        $events->listen(
            PostWasDeleted::class,
            'Coyote\Listeners\FlagSubscriber@handlePostDelete'
        );

        $events->listen(
            MicroblogDeleted::class,
            'Coyote\Listeners\FlagSubscriber@handleMicroblogDelete'
        );
    }
}
