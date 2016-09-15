<?php

namespace Coyote\Services\Stream\Render;

use Coyote\Services\Stream;

class Comment extends Render
{
    /**
     * @return string
     */
    public function target()
    {
        return link_to(
            $this->stream['object.url'],
            str_limit($this->stream['target.displayName'], 64),
            ['title' => $this->stream['target.displayName']]
        );
    }
}
