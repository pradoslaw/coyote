<?php

namespace Coyote\Services\Elasticsearch;

class MatchAll implements DslInterface
{
    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return [
            'match_all' => (object) []
        ];
    }
}
