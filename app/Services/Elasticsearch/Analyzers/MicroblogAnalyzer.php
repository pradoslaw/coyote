<?php

namespace Coyote\Services\Elasticsearch\Analyzers;

use Coyote\Services\Parser\Factories\MicroblogFactory as Parser;

class MicroblogAnalyzer implements AnalyzerInterface
{
    /**
     * @var Parser
     */
    private $parser;

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
        $data['text'] = strip_tags($this->parser->parse($data['text']));

        return $data;
    }
}
