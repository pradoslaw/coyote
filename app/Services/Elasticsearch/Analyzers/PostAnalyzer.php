<?php

namespace Coyote\Services\Elasticsearch\Analyzers;

use Coyote\Services\Parser\Factories\PostFactory as Parser;

class PostAnalyzer extends AbstractAnalyzer
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
        $data['topic']['subject'] = htmlspecialchars($data['topic']['subject']);
        $data['text'] = $this->stripHtml($data['text']);

        return $data;
    }
}
