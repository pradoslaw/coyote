<?php

namespace Coyote\Services\Stream\Render;

class Block extends Render
{
    /**
     * @return mixed
     */
    protected function excerpt()
    {
        return $this->stream['object.content'];
    }

    /**
     * @return string
     */
    protected function object()
    {
        return parent::object() . ': ' . $this->stream['object.displayName'];
    }
}
