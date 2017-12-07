<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\Filters\Range;
use Coyote\Services\Elasticsearch\MultiMatch;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\Sort;

class FbBuilder extends QueryBuilder
{
    /**
     * @var string
     */
    protected $query;

    /**
     * @param string $query
     */
    public function setLanguage($query)
    {
        $this->query = strtolower($query);
    }

    public function onlyFromLastWeek()
    {
        $this->must(new Range('boost_at', ['gt' => 'now-7d']));
    }

    /**
     * @return array
     */
    public function build()
    {
        $this->must(new MultiMatch($this->query, ['title^3', 'tags.original^2']));
        $this->sort(new Sort('score', 'desc'));
        $this->size(0, 100);

        return parent::build();
    }
}
