<?php
namespace Coyote\Services\Parser\Parsers;

class Latex extends HashParser implements Parser
{
    protected function parseHashed(string $text): string
    {
        return \preg_replace_callback(
            "#<tex>(.*?)</tex>#si",
            fn(array $match): string => \sPrintF(
                '<img src="https://latex.codecogs.com/svg.latex?%s" alt="%s" class="latex">',
                \rawUrlEncode($match[1]),
                \htmlSpecialChars($match[1]),
            ),
            $text,
        );
    }
}
