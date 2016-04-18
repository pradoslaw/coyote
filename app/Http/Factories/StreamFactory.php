<?php

namespace Coyote\Http\Factories;

use Coyote\Services\Stream\Stream;

trait StreamFactory
{
    /**
     * @return \Coyote\Services\Stream\Stream
     */
    protected function getStreamFactory()
    {
        return app(Stream::class);
    }
}
