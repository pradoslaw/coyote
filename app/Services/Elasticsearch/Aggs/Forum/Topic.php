<?php

namespace Coyote\Services\Elasticsearch\Aggs\Forum;

use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Topic extends Aggs\Terms implements DslInterface
{
    public function __construct()
    {
        parent::__construct('topic_id', 'topic_id', 10);
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = parent::apply($queryBuilder);
        $body['aggs'][$this->name] = array_merge_recursive($body['aggs'][$this->name], [
            'aggs' => [
                'top_hits' => [
                    'top_hits' => [
                        'size' => 1
                    ]
                ]
            ]
        ]);

        return $body;
    }
}
