<?php

namespace Coyote\Services\Stream\Render;

class Topic extends Render
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
    protected function object()
    {
        return $this->objectName();
    }

    /**
     * @return mixed
     */
    protected function source()
    {
        return $this->stream['object.forum.name'];
    }

    /**
     * @return string
     */
    protected function excerpt()
    {
        return $this->stream['object.reasonName'] ?: $this->stream['object.excerpt'];
    }
}
