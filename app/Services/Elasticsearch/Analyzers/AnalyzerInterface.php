<?php

namespace Coyote\Services\Elasticsearch\Analyzers;

interface AnalyzerInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function analyze(array $data): array;
}
