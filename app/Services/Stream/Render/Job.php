<?php

namespace Coyote\Services\Stream\Render;

class Job extends Render
{
    public function offer()
    {
        return $this->objectName();
    }
}
