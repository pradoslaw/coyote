<?php
namespace Coyote\Services\Parser;

use Coyote\Services\Parser\Parsers\Parser;

class CompositeParser
{
    private array $parsers = [];

    public function attach(Parser $parser): void
    {
        $this->parsers[] = $parser;
    }

    public function removeAll(): void
    {
        $this->parsers = [];
    }

    public function parse(string $text): string
    {
        foreach ($this->parsers as $parser) {
            $text = $parser->parse($text);
        }
        return $text;
    }
}
