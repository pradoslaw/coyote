<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Services\UrlBuilder\UrlBuilder;

class Comment extends ObjectAbstract
{
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
        $this->displayName = excerpt($microblog->html);
    }

    /**
     * @param \Coyote\Post $post
     * @param \Coyote\Post\Comment $comment
     * @param \Coyote\Topic $topic
     */
    private function post($post, $comment, $topic)
    {
        $this->id = $comment->id;
        $this->displayName = excerpt($comment->html);
        $this->url = UrlBuilder::topic($topic) . '?p=' . $post->id . '#comment-' . $comment->id;
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @param \Coyote\Wiki\Comment $comment
     */
    private function wiki($wiki, $comment)
    {
        $this->id = $comment->id;
        $this->displayName = excerpt($comment->html);
        $this->url = UrlBuilder::wikiComment($wiki, $comment->id);
    }

    /**
     * @param \Coyote\Job $job
     * @param \Coyote\Job\Comment $comment
     */
    private function job($job, $comment)
    {
        $this->id = $comment->id;
        $this->displayName = excerpt($comment->html);
        $this->url = UrlBuilder::jobComment($job, $comment->id);
    }
}
