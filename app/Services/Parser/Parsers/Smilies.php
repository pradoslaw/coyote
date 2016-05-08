<?php

namespace Coyote\Services\Parser\Parsers;

/**
 * Class Smilies
 */
class Smilies implements ParserInterface
{
    use Hash;

    private $smilies = [
        ':)'            => 'smile.gif'
    ];

    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        $text = $this->hashBlock($text, ['code', 'a']);
        $text = $this->hashInline($text, 'img');

        $patterns = $replacements = [];

        while (list($var, $value) = each($this->smilies)) {
            $patterns[] = '#(?<=^|[\n ]|\.)' . preg_quote($var, '#') . '#';
            $replacements[] = '<img class="img-smile" alt="' . $var . '" title="' . $var . '" src="' . cdn('img/smilies/' . $value) . '" />';
        }
        reset($this->smilies);

        $text = substr(preg_replace($patterns, $replacements, ' ' . $text . ' '), 1, -1);
        $text = $this->unhash($text);

        return $text;
    }
}
