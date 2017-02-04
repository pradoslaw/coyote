<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\Functions\Random;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryString;

class AdBuilder extends SearchBuilder
{
    /**
     * @var string|null
     */
    protected $sessionId = null;

    /**
     * @param string $sessionId
     */
    public function setSessionId(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @param array $tags
     */
    public function boostTags(array $tags)
    {
        $this->should(new QueryString(implode(' ', $tags), ['title^2', 'tags^2', 'description'], 3));
    }

    /**
     * @return array
     */
    public function build()
    {
        $this->setupFilters();
        $this->setupScoreFunctions();

        $this->score(new Random($this->sessionId));
        $this->size(0, 4);

        $this->source([
            'id',
            'title',
            'slug',
            'is_remote',
            'remote_range',
            'firm.*',
            'locations',
            'tags',
            'currency_name',
            'salary_from',
            'salary_to'
        ]);

        return QueryBuilder::build();
    }

    public function setSort($sort)
    {
        $this->sort($sort);
    }
}
