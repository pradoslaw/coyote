<?php

namespace Coyote\Listeners;

use Coyote\Events\ForumWasSaved;
use Coyote\Events\TopicWasMoved;
use Coyote\Topic;
use Illuminate\Contracts\Queue\ShouldQueue;

class IndexCategory implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  ForumWasSaved  $event
     * @return void
     */
    public function handle(ForumWasSaved $event)
    {
        if (!$event->original || $event->forum->parent_id === $event->original['parent_id']) {
            return;
        }

        $event->forum->hasMany(Topic::class)->chunk(100, function ($topics) {
            foreach ($topics as $topic) {
                event(new TopicWasMoved($topic));
            }
        });
    }
}
