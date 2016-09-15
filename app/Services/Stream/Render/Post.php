<?php

namespace Coyote\Services\Stream\Render;

class Post extends Render
{
    /**
     * @return mixed|string
     */
    protected function actor()
    {
        // author can be an anonymous user...
        if (!$this->stream['actor.id']) {
            return $this->stream['actor.displayName'];
        }

        return parent::actor();
    }

    /**
     * @return string
     */
    protected function target()
    {
        return link_to(
            $this->stream['object.url'],
            str_limit($this->stream['target.displayName'], 64),
            ['title' => $this->stream['target.displayName']]
        );
    }

    /**
     * @return mixed
     */
    protected function excerpt()
    {
        return $this->stream['object.reasonName'] ?: $this->stream['object.displayName'];
    }
}
