<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2016-12-30
 * Time: 22:07
 */

namespace Coyote\Services\Stream\Render;

class Application extends Render
{
    /**
     * @return mixed|string
     */
    protected function actor()
    {
        // author can be an anonymous user...
        if (!$this->stream['actor.id']) {
            return $this->stream['object.displayName'];
        }

        return parent::actor();
    }

    /**
     * @return string
     */
    public function object()
    {
        return (string) trans('stream.nouns.' . $this->stream['object.objectType']);
    }
}
