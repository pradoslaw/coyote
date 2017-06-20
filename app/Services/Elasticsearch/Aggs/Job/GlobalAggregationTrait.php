<?php

namespace Coyote\Services\Elasticsearch\Aggs\Job;

trait GlobalAggregationTrait
{
    /**
     * @param array $body
     * @return array
     */
    protected function wrapGlobal(array $body): array
    {
        $nested = $body['aggs'][$this->name];

        if (!isset($body['aggs']['global'])) {
            $body['aggs']['global'] = [];
        }

        $body['aggs']['global'] = array_merge_recursive((array) $body['aggs']['global'], [
            'aggs'      => [$this->name => $nested]
        ]);

        // this field MUST NOT be array
        $body['aggs']['global']['global'] = (object) [];

        return $body;
    }
}
