<?php

namespace Coyote\Elasticsearch\Aggs\Job;

use Coyote\Elasticsearch\Aggs;
use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\QueryBuilderInterface;

class Location extends Aggs\Nested implements DslInterface
{
    public function __construct()
    {
        parent::__construct('locations', 'city');
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = parent::apply($queryBuilder);
        $nested = $body['aggs'][$this->name];

        $body['aggs']['global'] = [
            'global'    => (object) [],
            'aggs'      => [$this->name => $nested]
        ];

        return $body;
    }
}