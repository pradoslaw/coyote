<?php

namespace Coyote\Services\Stream\Render;

class Wiki extends Render
{
    /**
     * @return string
     */
    public function object()
    {
        return (string) trans('stream.nouns.' . array_get($this->stream, 'object.objectType'));
    }

    /**
     * @return string
     */
    protected function title()
    {
        return $this->objectName();
    }

    /**
     * @return mixed|null
     */
    protected function excerpt()
    {
        return array_get($this->stream, 'object.excerpt') ?? null;
    }
}
