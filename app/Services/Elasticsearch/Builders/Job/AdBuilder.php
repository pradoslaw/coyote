<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\Functions\Random;

class AdBuilder extends SearchBuilder
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @param string $sessionId
     */
    public function setSessionId(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return array
     */
    public function build()
    {
        $this->addFilters();
        $this->addFunctionScore();

        $this->scoreFunction(new Random());
        $this->size(0, 4);

        return parent::build();
    }

    public function setSort($sort)
    {
        $this->sort($sort);
    }
}
