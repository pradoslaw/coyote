<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Resources\PostResource;
use Coyote\Post;

class PostController extends BaseController
{
    /**
     * @param \Coyote\Post $post
     * @return void
     */
    public function subscribe($post)
    {
        $subscriber = $post->subscribers()->forUser($this->userId)->first();

        if ($subscriber) {
            $subscriber->delete();
        } else {
            $post->subscribers()->create(['user_id' => $this->userId]);
        }
    }

    public function show(Post $post)
    {
        $this->authorize('access', $post->forum);

        PostResource::withoutWrapping();

        return new PostResource($post);
    }
}
