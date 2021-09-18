<?php

namespace Coyote\Events;

use Coyote\Http\Resources\PostResource;
use Coyote\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PostSaved implements ShouldBroadcast
{
    use SerializesModels, InteractsWithSockets;

    /**
     * @var Post
     */
    public Post $post;

    /**
     * @var bool
     */
    public bool $wasRecentlyCreated;

    /**
     * Create a new event instance.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
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
