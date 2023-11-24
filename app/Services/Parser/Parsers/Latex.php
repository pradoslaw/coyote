<?php
namespace Coyote\Services\Parser\Parsers;

class Latex extends HashParser implements Parser
{
    protected function parseHashed(string $text): string
    {
        return \preg_replace_callback(
            "#<tex>(.*?)</tex>#si",
            function ($match) {
                return sprintf(
                    '<img src="https://latex.codecogs.com/gif.latex?%s" alt="%s">',
                    rawurlencode($match[1]),
                    htmlspecialchars($match[1])
                );
            },
            $text
        );
    }
}
