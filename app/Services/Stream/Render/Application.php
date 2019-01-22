<?php

namespace Coyote\Services\Stream\Render;

class Application extends Render
{
    /**
     * @return mixed|string
     */
    protected function actor()
    {
        // author can be an anonymous user...
        if (!array_get($this->stream, 'actor.id')) {
            return array_get($this->stream, 'object.displayName');
        }

        return parent::actor();
    }

    /**
     * @return string
     */
    public function object()
    {
        return (string) trans('stream.nouns.' . array_get($this->stream, 'object.objectType'));
    }
}
