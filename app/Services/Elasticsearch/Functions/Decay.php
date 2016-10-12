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
     * @var float
     */
    protected $decay;

    /**
     * @var string
     */
    protected $decayFunction = 'gauss';

    /**
     * @param string $field
     * @param string $scale
     * @param float $decay
     */
    public function __construct($field, $scale, $decay = 0.5)
    {
        $this->field = $field;
        $this->scale = $scale;
        $this->decay = $decay;
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
                    'scale' => $this->scale,
                    'decay' => $this->decay
                ]
            ]
        ];

        return $body;
    }
}
