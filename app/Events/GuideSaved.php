<?php

namespace Coyote\Events;

use Coyote\Models\Guide;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class GuideSaved
{
    use SerializesModels, InteractsWithSockets;

    public function __construct(public Guide $guide)
    {
    }
}
