<?php
namespace Coyote\Services\Parser\Parsers;

use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

class Smilies extends HashParser implements Parser
{
    private array $smilies = [
        ':)'  => 'smile.gif',
        ':-)' => 'smile.gif',
        ';)'  => 'wink.gif',
        ';-)' => 'wink.gif',
        ':-|' => 'neutral.gif',
        ':D'  => 'laugh.gif',
        ':-D' => 'laugh.gif',
        ':('  => 'sad.gif',
        ':-(' => 'sad.gif',
        ':P'  => 'tongue1.gif',
        ':p'  => 'tongue1.gif',
        ':-P' => 'tongue1.gif',
        ':-/' => 'confused.gif',
        ':/'  => 'damn.gif',
        ':['  => 'mad.gif',
        ':-[' => 'mad.gif',
        ':|'  => 'zonk.gif',
        ':]'  => 'squared.gif',
        ':d'  => 'teeth.gif',
    ];

    protected function parseHashed(string $text): string
    {
        return Pattern::template('(?<=^|[\n \>]|\.)(@)')
            ->alteration(array_keys($this->smilies))
            ->replace($text)
            ->callback(function (Detail $match) {
                $smiley = $match->get(1);
                $link = $this->smilies[$smiley];
                return '<img class="img-smile" alt="' . $smiley . '" title="' . $smiley . '" src="/img/smilies/' . $link . '">';
            });
    }
}
