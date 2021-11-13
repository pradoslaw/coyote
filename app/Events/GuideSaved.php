<?php

namespace Coyote\Events;

use Coyote\Guide;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class GuideSaved
{
    use SerializesModels, InteractsWithSockets;

    public function __construct(public Guide $guide)
    {
    }
}
