<?php

namespace Coyote\Services\Elasticsearch\Response;

use Coyote\Services\Elasticsearch\Response;
use Coyote\Services\Elasticsearch\ResponseInterface;

class TopHits extends Response implements ResponseInterface
{
    /**
     * @param array $response
     */
    public function __construct($response)
    {
        if (isset($response['hits'])) {
            $this->hits = $this->collect($this->getResults($response));
            $this->totalHits = count($this->hits);

            if (isset($response['aggregations'])) {
                $this->aggregations = ($response['aggregations']);
            }
        }
    }

    /**
     * @param array $response
     * @return array
     */
    protected function getResults($response)
    {
        $result = [];

        foreach ($response['aggregations']['topic_id']['buckets'] as $bucket) {
            $hit = $bucket['top_hits']['hits']['hits'][0];

            $result[] = $hit;
        }

        return $result;
    }
}
