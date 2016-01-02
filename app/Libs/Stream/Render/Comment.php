<?php

namespace Coyote\Stream\Render;

use Coyote\Stream;

class Comment extends Render
{
    /**
     * @return string
     */
    public function target()
    {
        return link_to(
            $this->stream['object.url'],
            str_limit($this->stream['target.displayName'], 48)
        );
    }
}
