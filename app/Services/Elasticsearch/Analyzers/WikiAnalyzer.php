<?php

namespace Coyote\Services\Elasticsearch\Analyzers;

use Coyote\Services\Parser\Factories\WikiFactory as Parser;

class WikiAnalyzer extends AbstractAnalyzer
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
    public function analyze(array $data): array
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
