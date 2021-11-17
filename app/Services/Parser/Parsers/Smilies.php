<?php

namespace Coyote\Services\Parser\Parsers;

use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

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

        $text = Pattern::template('(?<=^|[\n \>]|\.)(@)')
            ->alteration(array_keys($this->smilies))
            ->replace($text)
            ->callback(function (Detail $match) {
                $smiley = $match->get(1);
                $link = $this->smilies[$smiley];
                return '<img class="img-smile" alt="' . $smiley . '" title="' . $smiley . '" src="/img/smilies/' . $link . '">';
            });

        $text = $this->unhash($text);

        return $text;
    }
}
