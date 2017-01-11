<?php

namespace Coyote\Services\Elasticsearch\Functions;

use Coyote\Services\Elasticsearch\FunctionScore;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Random extends FunctionScore
{
    /**
     * @var string|null
     */
    protected $sessionId;

    /**
     * @param string|null $sessionId
     */
    public function __construct(string $sessionId = null)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $this->wrap($queryBuilder);

        $body['query']['function_score']['functions'][] = [
            'random_score' => [
                'seed' => $this->sessionId
            ]
        ];

        return $body;
    }
}
