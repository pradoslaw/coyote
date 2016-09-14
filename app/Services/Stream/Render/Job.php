<?php

namespace Coyote\Services\Stream\Render;

class Job extends Render
{
    /**
     * @return string
     */
    public function offer()
    {
        return $this->objectName();
    }

    /**
     * @return string
     */
    public function excerpt()
    {
        return '';
    }
}
