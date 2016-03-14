<?php

namespace Coyote\Listeners;

use Coyote\Events\PostWasDeleted;
use Coyote\Events\PostWasSaved;
use Coyote\Post;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostListener implements ShouldQueue
{
    /**
     * @param PostWasSaved $event
     */
    public function onPostSave(PostWasSaved $event)
    {
        $this->fireJobs(function () use ($event) {
            $event->post->putToIndex();
        });
    }

    /**
     * @param PostWasDeleted $event
     */
    public function onPostDelete(PostWasDeleted $event)
    {
        $this->fireJobs(function () use ($event) {
            Post::withTrashed()->find($event->post['id'])->deleteFromIndex();
        });
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

    /**
     * @param \Closure $closure
     * @throws \Exception
     */
    private function fireJobs(\Closure $closure)
    {
        try {
            $closure();
        } catch (\Exception $e) {
            if (config('queue.default') !== 'sync') {
                throw $e;
            }
        }
    }
}
