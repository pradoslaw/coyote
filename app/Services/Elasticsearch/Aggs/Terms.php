<?php

namespace Coyote\Services\Elasticsearch\Aggs;

use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Terms extends Aggs implements DslInterface
{
    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $queryBuilder->getBody();

        $body['aggs'][$this->name] = [
                'terms' => [
                    'field' => $this->field,
                    'size'  => $this->size
                ]

        ];

        return $body;
    }
}
