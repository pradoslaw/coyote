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
        if (!array_has($this->stream, 'actor.id')) {
            return array_get($this->stream, 'actor.displayName');
        }

        return parent::actor();
    }

    /**
     * @return string
     */
    protected function target()
    {
        return link_to(
            array_get($this->stream, 'object.url'),
            str_limit(array_get($this->stream, 'target.displayName'), 64),
            ['title' => array_get($this->stream, 'target.displayName')]
        );
    }

    /**
     * @return mixed
     */
    protected function excerpt()
    {
        return array_get($this->stream, 'object.reasonName') ?: array_get($this->stream, 'object.displayName');
    }
}
