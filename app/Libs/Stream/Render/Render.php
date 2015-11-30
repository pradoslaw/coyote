<?php

namespace Coyote\Stream\Render;

use Coyote\Stream;

abstract class Render
{
    protected $stream;

    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }

    protected function actor()
    {
        return link_to(
            $this->stream['actor.url'],
            $this->stream['actor.displayName'],
            ['data-user-id' => $this->stream['actor.id']]
        );
    }

    protected function excerpt()
    {
        return $this->stream['object.displayName'];
    }

    protected function verb()
    {
        return trans('stream.' . $this->stream['verb']);
    }

    protected function object()
    {
        return link_to(
            $this->stream['object.url'],
            trans('stream.' . $this->stream['object.objectType'])
        );
    }

    public function target()
    {
        return link_to(
            $this->stream['target.url'],
            str_limit($this->stream['target.displayName'], 48)
        );
    }

    public function render()
    {
        $this->stream['headline'] = trans('stream.headline.' . $this->stream['object.objectType'], [
            'actor'     => $this->actor(),
            'verb'      => $this->verb(),
            'object'    => $this->object(),
            'target'    => $this->target()
        ]);
        $this->stream['excerpt'] = $this->excerpt();

        return $this->stream;
    }
}
