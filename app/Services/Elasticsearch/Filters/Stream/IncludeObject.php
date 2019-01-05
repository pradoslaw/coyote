<?php

namespace Coyote\Services\Elasticsearch\Filters\Stream;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class IncludeObject implements DslInterface
{
    /**
     * @var int
     */
    private $topicId;

    /**
     * @param int $topicId
     */
    public function __construct(int $topicId)
    {
        $this->topicId = $topicId;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed|object
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return (object) [
            "bool" => [
                'must' => [
                    ['term' => ['object.id' => $this->topicId]],
                    ['term' => ['object.objectType' => 'topic']]
                ]
            ]

        ];
    }
}
