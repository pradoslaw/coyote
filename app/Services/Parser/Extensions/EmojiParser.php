<?php
namespace Coyote\Services\Parser\Extensions;

use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

class EmojiParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::join(
            InlineParserMatch::string(':'),
            InlineParserMatch::regex('\w+'),
            InlineParserMatch::string(':'));
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        return $this->appendEmoji($inlineContext->getCursor(), $inlineContext);
    }

    private function appendEmoji(Cursor $cursor, InlineParserContext $context): bool
    {
        $previousState = $cursor->saveState();
        $emoji = $cursor->match('/^:\w+:/');
        if ($emoji === null) {
            $cursor->restoreState($previousState);
            return false;
        }
        $container = $context->getContainer();
        $container->appendChild(new EmojiNode(\subStr($emoji, 1, -1)));
        return true;
    }
}
