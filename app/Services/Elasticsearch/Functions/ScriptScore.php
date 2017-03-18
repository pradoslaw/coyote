<?php

namespace Coyote\Services\Elasticsearch\Functions;

use Coyote\Services\Elasticsearch\FunctionScore;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class ScriptScore extends FunctionScore
{
    protected $script;

    /**
     * @param string $script
     */
    public function __construct($script)
    {
        $this->script = $script;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $this->wrap($queryBuilder);
        $body['query']['function_score']['functions'][] = ['script_score' => $this->getSetup()];

        return $body;
    }

    /**
     * @return array
     */
    private function getSetup()
    {
        return [
            'script' => [
                'lang' => 'painless',
                'inline' => $this->script
            ]
        ];
    }
}
