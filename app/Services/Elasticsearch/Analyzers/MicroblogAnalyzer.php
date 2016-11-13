<?php

namespace Coyote\Services\Elasticsearch\Analyzers;

use Coyote\Services\Parser\Factories\MicroblogFactory as Parser;

class MicroblogAnalyzer extends AbstractAnalyzer
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
        $data['text'] = $this->stripHtml($data['text']);

        return $data;
    }
}
