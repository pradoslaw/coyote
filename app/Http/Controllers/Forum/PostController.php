<?php

namespace Coyote\Http\Controllers\Forum;

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
}
