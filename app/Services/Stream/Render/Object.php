<?php

namespace Coyote\Services\Stream\Render;

class Object extends Render
{
    /**
     * @return string
     */
    public function login()
    {
        return $this->stream['login'];
    }
}
