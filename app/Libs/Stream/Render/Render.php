<?php

namespace Coyote\Stream\Render;

use Coyote\Stream;

/**
 * Class Render
 * @package Coyote\Stream\Render
 */
abstract class Render
{
    protected $stream;

    /**
     * @param Stream $stream
     */
    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }

    public function render()
    {
        $translator = app('translator');
        $id = 'stream.headline.' . $this->stream['object.objectType'];

        if ($translator->has($id . ':' . $this->stream['verb'])) {
            $id .= ':' . $this->stream['verb'];
        }

        $message = $translator->get($id);
        $parameters = $this->makeParameters($message);

        $this->stream['headline'] = $translator->trans($id, $parameters);
        $this->stream['excerpt'] = $this->excerpt();

        return $this->stream;
    }

    protected function makeParameters($message)
    {
        $parameters = [];
        $offset = 0;

        while (($start = strpos($message, ':', $offset)) > -1) {
            $offset = $start + 1;
            $end = strpos($message, ' ', $offset);

            if ($end === false) {
                $end = strlen($message);
            }

            $parameter = substr($message, $start + 1, $end - 1 - $start);
            if (method_exists($this, $parameter)) {
                $parameters[$parameter] = $this->$parameter();
            }
        }

        return $parameters;
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

    protected function target()
    {
        return link_to(
            $this->stream['target.url'],
            str_limit($this->stream['target.displayName'], 48)
        );
    }
}
