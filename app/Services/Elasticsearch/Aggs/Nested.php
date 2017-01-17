<?php

namespace Coyote\Services\Elasticsearch\Aggs;

use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Nested extends Aggs implements DslInterface
{
    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $queryBuilder->getBody();

        $body['aggs'] = [
            $this->name => [
                'nested' => [
                    'path' => $this->name
                ],
                'aggs' => [
                    str_replace('.', '_', $this->field) => [
                        'terms' => [
                            'field' => $this->field,
                            'size' => 15
                        ]
                    ]
                ]
            ]
        ];

        return $body;
    }
}
