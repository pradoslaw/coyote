<?php

namespace Coyote\Listeners;

use Coyote\Events\ForumSaved;
use Coyote\Events\TopicMoved;
use Coyote\Topic;
use Illuminate\Contracts\Queue\ShouldQueue;

class IndexCategory implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  ForumSaved  $event
     * @return void
     */
    public function handle(ForumSaved $event)
    {
        if (!$event->original || $event->forum->parent_id === $event->original['parent_id']) {
            return;
        }

        $event->forum->hasMany(Topic::class)->chunk(100, function ($topics) {
            foreach ($topics as $topic) {
                event(new TopicMoved($topic));
            }
        });
    }
}
