<?php

namespace Coyote\Services\Elasticsearch;

abstract class FunctionScore implements DslInterface
{
    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    protected function wrap(QueryBuilderInterface $queryBuilder)
    {
        $body = $queryBuilder->getBody();

        if (isset($body['query']['function_score'])) {
            return $body;
        }

        $query = array_pull($body, 'query');

        array_set($body, 'query.function_score.query', $query);
        array_set($body, 'query.function_score.functions', []);

        return $body;
    }
}
