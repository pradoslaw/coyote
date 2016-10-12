<?php

namespace Coyote\Services\Elasticsearch\Functions;

use Coyote\Services\Elasticsearch\FunctionScore;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Decay extends FunctionScore
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $scale;

    /**
     * @var string
     */
    protected $decayFunction = 'gauss';

    /**
     * @param string $field
     * @param string $scale
     */
    public function __construct($field, $scale)
    {
        $this->field = $field;
        $this->scale = $scale;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $this->wrap($queryBuilder);

        $body['query']['function_score']['functions'][] = [
            $this->decayFunction => [
                $this->field => [
                    'scale' => $this->scale
                ]
            ]
        ];

        return $body;
    }
}
