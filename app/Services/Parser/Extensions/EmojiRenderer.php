<?php
namespace Coyote\Services\Parser\Extensions;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

class EmojiRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement|string
    {
        if ($node instanceof EmojiNode) {
            return $this->renderEmoji($node);
        }
        throw new \LogicException('Failed to parse markdown emoticons');
    }

    private function renderEmoji(EmojiNode $emoji): HtmlElement|string
    {
        if (Emoji::exists($emoji->code)) {
            return $this->htmlElement(new Emoji($emoji->code));
        }
        return '';
    }

    private function htmlElement(Emoji $emoji): HtmlElement
    {
        return new HtmlElement('img', [
            'class' => 'img-smile',
            'src'   => $this->cdnUrl($emoji),
            'alt'   => $emoji->unicodeCharacter,
            'title' => $emoji->title,
        ], '', selfClosing: true);
    }

    private function cdnUrl(Emoji $emoji): string
    {
        return "https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/svg/$emoji->unified.svg";
    }
}
