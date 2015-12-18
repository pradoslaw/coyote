<?php

namespace Coyote\Stream\Objects;

use Coyote\Topic as Model;
use Coyote\Forum;

class Topic extends Object
{
    /**
     * @param Model $topic
     * @param Forum $forum
     * @param string $text
     * @return $this
     */
    public function map(Model $topic, Forum $forum, $text)
    {
        $this->id = $topic->id;
        $this->url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);
        $this->excerpt = excerpt($text);
        $this->displayName = $topic->subject;
        $this->forum = ['name' => $forum->name, 'id' => $forum->id, 'path' => $forum->path];

        return $this;
    }
}
