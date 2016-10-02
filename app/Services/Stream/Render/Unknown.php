<?php

namespace Coyote\Services\Stream\Render;

class Unknown extends Render
{
    /**
     * @return string
     */
    public function login()
    {
        return $this->stream['login'];
    }
}
