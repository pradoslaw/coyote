<?php

namespace Coyote\Events;

use Illuminate\Queue\SerializesModels;
use Coyote\Wiki;

class WikiDeleted
{
    use SerializesModels;

    /**
     * @var array
     */
    public $wiki;

    /**
     * @param Wiki $wiki
     */
    public function __construct(Wiki $wiki)
    {
        $this->wiki = $wiki->toArray();
    }
}
