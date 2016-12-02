<?php

namespace Coyote\Services\Elasticsearch\CharFilters;

use Coyote\Services\Parser\Factories\PostFactory as Parser;

class TopicFilter extends CharFilter
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
        $data['subject'] = htmlspecialchars($data['subject']);

        foreach ($data['posts'] as &$post) {
            $post['text'] = $this->stripHtml($post['text']);
        }

        return $data;
    }
}
