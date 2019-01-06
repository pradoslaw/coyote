<?php

namespace Coyote\Listeners;

use Coyote\Events\StreamSaved;
use Illuminate\Contracts\Queue\ShouldQueue;

class IndexStream implements ShouldQueue
{
    /**
     * @param StreamSaved $event
     * @throws \Exception
     */
    public function handle(StreamSaved $event)
    {
        $event->stream->putToIndex();
    }
}
