<?php

namespace Coyote\Services\Elasticsearch\Analyzers;

use Coyote\Services\Parser\Factories\AbstractFactory as Parser;

abstract class AbstractAnalyzer implements AnalyzerInterface
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
