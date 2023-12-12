<?php
namespace Coyote\Services\Parser\Parsers;

use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Services\Parser\Extensions\EmojiRenderer;
use Incenteev\EmojiPattern\EmojiPattern;

class UnicodeEmojiSvg extends HashParser implements Parser
{
    protected function parseHashed(string $text): string
    {
        $pattern = EmojiPattern::getEmojiPattern();
        return \preg_replace_callback('/' . $pattern . '/u', function (array $match): string {
            return $this->imageHtml($match[0]);
        }, $text);
    }

    private function imageHtml(string $unicodeEmoji): string
    {
        $emoji = Emoji::fromUnicodeCharacter($unicodeEmoji);
        if ($emoji === null) {
            return $unicodeEmoji;
        }
        return EmojiRenderer::htmlElement($emoji);
    }
}
