<?php

namespace Coyote\Services\Elasticsearch\Filters\Stream;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class IncludeTarget implements DslInterface
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
                    ['term' => ['target.id' => $this->topicId]],
                    ['term' => ['target.objectType' => 'topic']]
                ]
            ]
        ];
    }
}
