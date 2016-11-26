<?php

namespace Coyote\Services\Stream\Render;

class Wiki extends Render
{
    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function object()
    {
        return trans('stream.nouns.' . $this->stream['object.objectType']);
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
        return $this->stream['object.excerpt'] ?? null;
    }
}
