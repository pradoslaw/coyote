<?php

namespace Coyote\Events;

use Coyote\Http\Resources\PostResource;
use Coyote\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PostSaved extends BroadcastEvent implements ShouldBroadcast
{
    use SerializesModels, InteractsWithSockets;

    public bool $wasRecentlyCreated;

    public function __construct(
        public Post    $post,
        public ?string $previousPostHtml = null,
    )
    {
        $this->wasRecentlyCreated = $post->wasRecentlyCreated;
    }

    /**
     * @return Channel|Channel[]
     */
    public function broadcastOn()
    {
        return new Channel('topic:' . $this->post->topic_id);
    }

    /**
     * @return array
     */
    public function broadcastWith(): array
    {
        $request = clone request();
        // assign null to user.
        $request->setUserResolver(function () {
            return null;
        });

        return (new PostResource($this->post))->resolve($request);
    }
}
