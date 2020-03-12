<?php

namespace Coyote\Services\Parser\Parsers;

class Latex extends Parser implements ParserInterface
{
    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        $text = $this->hashBlock($text, ['code', 'a']);
        $text = $this->hashInline($text, 'img');

        $text = preg_replace_callback(
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

        $text = $this->unhash($text);

        return $text;
    }
}
