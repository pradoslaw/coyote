<?php

namespace Coyote\Services\Elasticsearch\Aggs\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class TopSpot implements DslInterface
{
    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $queryBuilder->getBody();

        $body['aggs']['premium_listing'] = [
            'terms' => [
                'field' => 'is_on_top',
                'size' => 3,
                'order' => [
                    'top_hit' => 'desc'
                ]
            ],
            'aggs' => [
                'premium_listing' => [
                    'top_hits' => (object) []
                ],
                'top_hit' => [
                    'max' => [
                        'script' => ['inline' => '_score']
                    ]
                ]
            ]
        ];

        return $body;
    }
}
