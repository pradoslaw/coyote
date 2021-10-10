<?php

namespace Coyote\Events;

use Coyote\Wiki;
use Illuminate\Queue\SerializesModels;

class WikiSaved
{
    use SerializesModels;

    /**
     * @var Wiki
     */
    public $wiki;

    /**
     * @var string
     */
    public $host;

    /**
     * @param Wiki $wiki
     */
    public function __construct(Wiki $wiki)
    {
        $this->wiki = $wiki;
        $this->host = request()->getHost();
    }
}
