<?php

namespace Coyote\Events;

use Coyote\Models\Guide;
use Illuminate\Queue\SerializesModels;

class GuideDeleted
{
    use SerializesModels;

    public array $guide;

    public function __construct(Guide $guide)
    {
        $this->guide = $guide->toArray();
    }
}
