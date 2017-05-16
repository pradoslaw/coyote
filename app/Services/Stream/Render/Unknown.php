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

    /**
     * @return string
     */
    public function email()
    {
        return $this->stream['email'];
    }
}
