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
     * @var int
     */
    protected $weight;

    /**
     * @param string|null $sessionId
     * @param int $weight
     */
    public function __construct(string $sessionId = null, int $weight = 1)
    {
        $this->sessionId = $sessionId;
        $this->weight = $weight;
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
                'seed' => $this->sessionId,
            ],
            'weight' => $this->weight
        ];

        return $body;
    }
}
