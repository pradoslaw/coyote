<?php

namespace Coyote\Events;

use Coyote\Http\Resources\MicroblogResource;
use Coyote\Microblog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MicroblogSaved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Microblog $microblog;
    public bool $wasRecentlyCreated;
    public bool $wasContentChanged;

    /**
     * Create a new event instance.
     *
     * @param Microblog $microblog
     */
    public function __construct(Microblog $microblog)
    {
        $this->microblog = $microblog;
        $this->wasRecentlyCreated = $microblog->wasRecentlyCreated;
        $this->wasContentChanged = $microblog->wasRecentlyCreated || $microblog->wasChanged(['text']);
    }

    /**
     * @return Channel|Channel[]
     */
    public function broadcastOn()
    {
        return new Channel('microblog');
    }

    /**
     * @return array
     */
    public function broadcastWith()
    {
        $request = clone request();
        // assign null to user.
        $request->setUserResolver(function () {
            return null;
        });

        // don't forget to load assets before sending JSON via websocket
        $this->microblog->load('assets');

        return (new MicroblogResource($this->microblog))->resolve($request);
    }

    /**
     * @return string
     */
    public function broadcastAs()
    {
        return class_basename(self::class);
    }
}
