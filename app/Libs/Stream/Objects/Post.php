<?php

namespace Coyote\Stream\Objects;

use Coyote\Post as Model;

class Post extends Object
{
    public function map(Model $post)
    {
        $this->id = $post->id;
        $this->displayName = excerpt($post->text);

        return $this;
    }
}