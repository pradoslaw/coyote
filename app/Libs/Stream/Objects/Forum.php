<?php

namespace Coyote\Stream\Objects;

use Coyote\Forum as Model;

class Forum extends Object
{
    /**
     * @param Model $forum
     * @return mixed
     */
    public function map(Model $forum)
    {
        $this->id = $forum->id;
        $this->displayName = $forum->name;
        $this->url = route('forum.category', [$forum->path], false);

        return $this;
    }
}
