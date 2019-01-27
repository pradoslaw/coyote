<?php

namespace Coyote\Services\Stream\Render;

class Comment extends Render
{
    /**
     * @return string
     */
    public function target()
    {
        return link_to(
            array_get($this->stream, 'target.url'),
            str_limit(array_get($this->stream, 'target.displayName'), 64),
            ['title' => array_get($this->stream, 'target.displayName')]
        );
    }
}
