<?php

namespace Coyote\Services\Elasticsearch\Analyzers;

use Coyote\Services\Parser\Factories\JobFactory as Parser;

class JobAnalyzer extends AbstractAnalyzer
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
        $data['description'] = $this->stripHtml($data['description']);

        if (!empty($data['requirements'])) {
            $data['requirements'] = $this->stripHtml($data['requirements']);
        }

        if (!empty($data['recruitment'])) {
            $data['recruitment'] = $this->stripHtml($data['recruitment']);
        }

        return $data;
    }
}
