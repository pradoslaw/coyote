<?php

namespace Coyote\Stream\Render;

use Coyote\Stream;

class Topic extends Render
{
    /**
     * @return mixed|string
     */
    protected function actor()
    {
        // author can be an anonymous user...
        if (!$this->stream['actor.id']) {
            return $this->stream['actor.displayName'];
        }

        return parent::actor();
    }

    /**
     * @return string
     */
    protected function object()
    {
        return link_to(
            $this->stream['object.url'],
            excerpt($this->stream['object.displayName']),
            ['title' => $this->stream['object.displayName']]
        );
    }

    /**
     * @return string
     */
    public function excerpt()
    {
        return $this->stream['object.excerpt'];
    }
}
