<?php

namespace Coyote\Stream\Objects;

use Coyote\Microblog as Model;

class Comment extends Object
{
    public function map(...$args)
    {
        $object = $args[0];

        $class = class_basename($object);
        if (!method_exists($this, $class)) {
            throw new \Exception("There is not method called $class");
        }

        $this->$class(...$args);

        return $this;
    }

    private function microblog($microblog)
    {
        $this->id = $microblog->id;
        $this->url = route('microblog.view', [$microblog->parent_id], false) . '#comment-' . $microblog->id;
        $this->displayName = excerpt($microblog->text);
    }

    private function post($post, $comment, $forum, $topic)
    {
        $this->id = $comment->id;
        $this->displayName = excerpt($comment->text);
        $this->url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false) . '?p=' . $post->id . '#comment-' . $comment->id;
    }
}
