<?php

namespace Coyote\Services\Elasticsearch\Functions;

use Coyote\Services\Elasticsearch\FunctionScore;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class FieldValueFactor extends FunctionScore
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var null|string
     */
    protected $modifier;

    /**
     * @var null|string
     */
    protected $factor;

    /**
     * @param string $field
     * @param null|string $modifier
     * @param null|string $factor
     */
    public function __construct($field, $modifier = null, $factor = null)
    {
        $this->field = $field;
        $this->modifier = $modifier;
        $this->factor = $factor;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $this->wrap($queryBuilder);
        $body['query']['function_score']['functions'][] = ['field_value_factor' => $this->getSetup()];

        return $body;
    }

    /**
     * @return array
     */
    private function getSetup()
    {
        $result = ['missing' => 1];

        foreach (['field', 'modifier', 'factor'] as $option) {
            if ($this->{$option} !== null) {
                $result[$option] = $this->{$option};
            }
        }

        return $result;
    }
}
