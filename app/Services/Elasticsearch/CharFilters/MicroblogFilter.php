<?php

namespace Coyote\Services\Elasticsearch\CharFilters;

use Coyote\Services\Parser\Factories\MicroblogFactory as Parser;

class MicroblogFilter extends CharFilter
{
    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param array $data
     * @return array
     */
    public function filter(array $data): array
    {
        $data['text'] = $this->stripHtml($data['text']);

        return $data;
    }
}
