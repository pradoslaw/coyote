<?php

namespace Coyote\Services\Elasticsearch\Analyzers;

use Coyote\Services\Parser\Factories\PostFactory as Parser;

class TopicAnalyzer extends AbstractAnalyzer
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
        $data['subject'] = htmlspecialchars($data['subject']);

        foreach ($data['posts'] as &$post) {
            $post['text'] = $this->stripHtml($post['text']);
        }

        return $data;
    }
}
