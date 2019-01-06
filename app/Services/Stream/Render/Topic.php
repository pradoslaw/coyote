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
        if (!array_has($this->stream, 'actor.id')) {
            return array_get($this->stream, 'actor.displayName');
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
        return array_get($this->stream, 'object.forum.name');
    }

    /**
     * @return string
     */
    protected function excerpt()
    {
        return array_get($this->stream, 'object.reasonName') ?: array_get($this->stream, 'object.excerpt');
    }
}
