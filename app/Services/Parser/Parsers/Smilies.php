<?php

namespace Coyote\Services\Parser\Parsers;

/**
 * Class Smilies
 */
class Smilies extends Parser implements ParserInterface
{
    private $smilies = [
        ':)'        => 'smile.gif',
        ':-)'       => 'smile.gif',
        ';)'        => 'wink.gif',
        ';-)'       => 'wink.gif',
        ':-|'       => 'neutral.gif',
        ':D'        => 'laugh.gif',
        ':-D'       => 'laugh.gif',
        ':('        => 'sad.gif',
        ':-('       => 'sad.gif',
        ':P'        => 'tongue1.gif',
        ':p'        => 'tongue1.gif',
        ':-P'       => 'tongue1.gif',
        ':-/'       => 'confused.gif',
        ':/'        => 'damn.gif',
        ':['        => 'mad.gif',
        ':-['       => 'mad.gif',
        ':|'        => 'zonk.gif',
        ':]'        => 'squared.gif',
        ':d'        => 'teeth.gif'
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

        foreach ($this->smilies as $var => $value) {
            $patterns[] = '#(?<=^|[\n \>]|\.)' . preg_quote($var) . '#';
            $replacements[] = '<img class="img-smile" alt="' . $var . '" title="' . $var . '" src="' . cdn('img/smilies/' . $value) . '">';
        }
        reset($this->smilies);

        $text = substr(preg_replace($patterns, $replacements, ' ' . $text . ' '), 1, -1);
        $text = $this->unhash($text);

        return $text;
    }
}
