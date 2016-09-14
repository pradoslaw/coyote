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
}
