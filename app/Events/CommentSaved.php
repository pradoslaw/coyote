<?php

namespace Coyote\Events;

use Coyote\Http\Resources\PostCommentResource;
use Coyote\Post\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class CommentSaved implements ShouldBroadcast
{
    use SerializesModels, InteractsWithSockets;

    /**
     * @var Comment
     */
    public $comment;

    /**
     * @var bool
     */
    public bool $wasRecentlyCreated;

    /**
     * CommentSaved constructor.
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->wasRecentlyCreated = $comment->wasRecentlyCreated;
    }

    /**
     * @return Channel|Channel[]
     */
    public function broadcastOn()
    {
        return new Channel('topic:' . $this->comment->post?->topic_id);
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

        return (new PostCommentResource($this->comment))->resolve($request);
    }

    /**
     * @return string
     */
    public function broadcastAs()
    {
        return class_basename(self::class);
    }
}
