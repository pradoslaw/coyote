<?php

namespace Coyote\Services\Elasticsearch;

interface ResponseInterface
{
    /**
     * Total Hits
     *
     * @return int
     */
    public function totalHits();

    /**
     * Get Hits
     *
     * Get the raw hits array from
     * Elasticsearch results.
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function getHits();

    /**
     * @return static
     */
    public function getHighlights();

    /**
     * @return \Illuminate\Support\Collection|array
     */
    public function getAggregations();
}
