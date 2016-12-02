<?php

namespace Coyote\Services\Elasticsearch\CharFilters;

use Coyote\Services\Parser\Factories\WikiFactory as Parser;

class WikiFilter extends CharFilter
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
        $data['title'] = htmlspecialchars($data['title']);

        if (!empty($data['text'])) {
            $data['text'] = $this->stripHtml($data['text']);
        }

        if (!empty($data['excerpt'])) {
            $data['excerpt'] = $this->stripHtml($data['excerpt']);
        }

        return $data;
    }
}
