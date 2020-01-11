<?php

namespace Coyote\Services\Stream\Render;

class Person extends Render
{
    // display user's email
    public function excerpt()
    {
        if (!empty(array_get($this->stream, 'object.email'))) {
            return 'E-mail: ' . array_get($this->stream, 'object.email');
        }
    }

    /**
     * @return string
     */
    protected function object()
    {
        return (string) trans('stream.nouns.' . array_get($this->stream, 'object.objectType')) . ' ' . link_to(
            array_get($this->stream, 'object.url'),
            array_get($this->stream, 'object.displayName')
        );
    }
}
