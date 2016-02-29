<?php

namespace Coyote\Listeners;

use Coyote\Events\PostWasDeleted;
use Coyote\Events\PostWasSaved;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostListener implements ShouldQueue
{
    /**
     * @param PostWasSaved $event
     */
    public function onPostSave(PostWasSaved $event)
    {
        //
    }

    /**
     * @param PostWasDeleted $event
     */
    public function onPostDelete(PostWasDeleted $event)
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\PostWasSaved',
            'Coyote\Listeners\PostListener@onPostSave'
        );

        $events->listen(
            'Coyote\Events\PostWasDeleted',
            'Coyote\Listeners\PostListener@onPostDelete'
        );
    }
}
