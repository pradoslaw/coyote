<?php

namespace Coyote\Events;

use Coyote\Stream;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class StreamSaved
{
    use Dispatchable, SerializesModels;

    /**
     * @var Stream
     */
    public $stream;

    /**
     * @param Stream $stream
     */
    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }
}
