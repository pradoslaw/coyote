<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Resources\PostResource;
use Coyote\Post;

class PostController extends BaseController
{
    public function show(Post $post)
    {
        $this->authorize('access', $post->forum);

        PostResource::withoutWrapping();

        return new PostResource($post);
    }
}
