<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Services\UrlBuilder;
use Coyote\Comment as Model;

class Comment extends ObjectAbstract
{
    private const EXCERPT_SIZE = 1024;

    /**
     * @param array ...$args
     * @return $this
     * @throws \Exception
     */
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

    /**
     * @param \Coyote\Microblog $microblog
     */
    private function microblog($microblog)
    {
        $this->id = $microblog->id;
        $this->url = UrlBuilder::microblogComment($microblog);
        $this->displayName = excerpt($microblog->html, self::EXCERPT_SIZE);
    }

    /**
     * @param \Coyote\Post $post
     * @param \Coyote\Post\Comment $comment
     * @param \Coyote\Topic $topic
     */
    private function post($post, $comment, $topic)
    {
        $this->id = $comment->id;
        $this->displayName = excerpt($comment->html, self::EXCERPT_SIZE);
        $this->url = UrlBuilder::topic($topic) . '?p=' . $post->id . '#comment-' . $comment->id;
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @param \Coyote\Wiki\Comment $comment
     */
    private function wiki($wiki, $comment)
    {
        $this->id = $comment->id;
        $this->displayName = excerpt($comment->html, self::EXCERPT_SIZE);
        $this->url = UrlBuilder::wikiComment($wiki, $comment->id);
    }

    public function comment(Model $comment)
    {
        $this->id = $comment->id;
        $this->displayName = excerpt($comment->html, self::EXCERPT_SIZE);
        $this->url = UrlBuilder::url($comment->resource) . '#comment-' . $comment->id;

        return $this;
    }
}
