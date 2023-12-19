<?php
namespace Coyote\Services\Parser\Parsers;

use Coyote\Services\Parser\Extensions\Emoji;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

class Smilies extends HashParser implements Parser
{
    public static array $smilies = [
        ':)'  => 'smile',
        ':-)' => 'twinkle',
        ';)'  => 'wink',
        ';-)' => 'wink',
        ':-|' => 'neutral',
        ':D'  => 'laugh',
        ':-D' => 'laugh',
        ':('  => 'frown',
        ':-(' => 'frown',
        ':P'  => 'tongue',
        ':p'  => 'tongue',
        ':-P' => 'tongue',
        ':-/' => 'diagonal_mouth',
        ':/'  => 'grimacing',
        ':['  => 'rage',
        ':-[' => 'rage',
        ':|'  => 'raised_eyebrow',
        ':]'  => 'smirk',
        ':d'  => 'happy',
    ];

    protected function parseHashed(string $text): string
    {
        return Pattern::template('(?<=^|[\n \>]|\.)@')
            ->alteration(\array_keys(self::$smilies))
            ->replace($text)
            ->callback(fn(Detail $match) => $this->emojiHtmlElement($match));
    }

    private function emojiHtmlElement(string $asciiEmoticon): string
    {
        return $this->imageHtml(new Emoji($this->emojiCode($asciiEmoticon)));
    }

    private function imageHtml(Emoji $emoji): string
    {
        $src = $this->imageUrl($emoji);
        return "<img class='img-smile' alt='$emoji->unicodeCharacter' title='$emoji->title' src='$src'>";
    }

    private function imageUrl(Emoji $emoji): string
    {
        return "https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/svg/$emoji->unified.svg";
    }

    private function emojiCode(string $asciiEmoticon): string
    {
        return self::$smilies[$asciiEmoticon];
    }
}
