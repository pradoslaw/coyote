<?php

namespace Coyote\Services\Elasticsearch\CharFilters;

interface CharFilterInteface
{
    /**
     * @param array $data
     * @return array
     */
    public function filter(array $data): array;
}
