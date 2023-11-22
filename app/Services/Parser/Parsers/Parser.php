<?php
namespace Coyote\Services\Parser\Parsers;

interface Parser
{
    public function parse(string $text): string;
}
