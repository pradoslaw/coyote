<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Services\UrlBuilder;

class Comment extends ObjectAbstract
{
    private const EXCERPT_SIZE = 1024;

    /**
     * @param mixed ...$args
     * @return $this
     * @throws \Exception
     */
    public function map(...$args)
    {
        $class = class_basename($args[0]);
        if (!method_exists($this, $class)) {
            throw new \Exception("There is not method called $class");
        }
        $this->$class(...$args);
        return $this;
    }

    private function microblog(\Coyote\Microblog $microblog): void
    {
        $this->id = $microblog->id;
        $this->url = UrlBuilder::microblogComment($microblog);
        $this->displayName = excerpt($microblog->html, self::EXCERPT_SIZE);
    }

    private function post(\Coyote\Post $post, \Coyote\Post\Comment $comment, \Coyote\Topic $topic): void
    {
        $this->id = $comment->id;
        $this->displayName = excerpt($comment->html, self::EXCERPT_SIZE);
        $this->url = UrlBuilder::topic($topic) . '?p=' . $post->id . '#comment-' . $comment->id;
    }

    private function wiki(\Coyote\Wiki $wiki, \Coyote\Wiki\Comment $comment): void
    {
        $this->id = $comment->id;
        $this->displayName = excerpt($comment->html, self::EXCERPT_SIZE);
        $this->url = UrlBuilder::wikiComment($wiki, $comment->id);
    }

    public function comment(\Coyote\Comment $comment)
    {
        $this->id = $comment->id;
        $this->displayName = excerpt($comment->html, self::EXCERPT_SIZE);
        $this->url = UrlBuilder::url($comment->resource) . '#comment-' . $comment->id;

        return $this;
    }
}
