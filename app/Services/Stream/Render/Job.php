<?php

namespace Coyote\Services\Stream\Render;

class Job extends Render
{
    public function offer()
    {
        return link_to(
            $this->stream['object.url'],
            $this->stream['object.displayName'],
            ['title' => $this->stream['object.displayName']]
        );
    }
}
