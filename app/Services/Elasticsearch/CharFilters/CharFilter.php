<?php

namespace Coyote\Services\Elasticsearch\CharFilters;

use Coyote\Services\Parser\Factories\AbstractFactory as Parser;

abstract class CharFilter implements CharFilterInteface
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @param string $value
     * @return string
     */
    protected function stripHtml($value)
    {
        return strip_tags($this->parser->parse($value));
    }
}
