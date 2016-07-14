<?php

namespace Coyote\Services\Stream\Render;

class Wiki extends Render
{
    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function object()
    {
        return trans('stream.' . $this->stream['object.objectType']);
    }

    /**
     * @return string
     */
    protected function title()
    {
        return link_to(
            $this->stream['object.url'],
            $this->stream['object.displayName'],
            ['title' => $this->stream['object.displayName']]
        );
    }
}
