<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Post as Model;

class Post extends Object
{
    /**
     * @param Model $post
     * @return $this
     */
    public function map(Model $post)
    {
        $this->id = $post->id;
        $this->displayName = excerpt($post->text);

        return $this;
    }

    /**
     * Parse text and then - pass object through map() method. We don't want to save raw markdown
     * into mongodb.
     *
     * @param Model $post
     * @return Post
     */
    public function markdown(Model $post)
    {
        $post->text = app('parser.post')->parse($post->text);

        return $this->map($post);
    }
}
