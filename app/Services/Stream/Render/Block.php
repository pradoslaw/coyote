<?php

namespace Coyote\Services\Stream\Render;

class Block extends Render
{
    /**
     * @return mixed
     */
    protected function excerpt()
    {
        return array_get($this->stream, 'object.content');
    }

    /**
     * @return string
     */
    protected function object()
    {
        return parent::object() . ': ' . array_get($this->stream, 'object.displayName');
    }
}
