<?php

namespace Coyote\Stream\Objects;

use Coyote\Topic as Model;
use Coyote\Forum;

class Topic extends Object
{
    /**
     * @param Model $topic
     * @param Forum $forum
     * @param string|null $text
     * @return $this
     */
    public function map(Model $topic, Forum $forum, $text = null)
    {
        $this->id = $topic->id;
        $this->url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);
        $this->displayName = $topic->subject;

        if ($text) {
            $this->excerpt = excerpt($text);
        }

        return $this;
    }
}
