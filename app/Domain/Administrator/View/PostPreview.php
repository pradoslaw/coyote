<?php
namespace Coyote\Domain\Administrator\View;

use Coyote\Domain\Html;

class PostPreview extends Html
{
    private InlineHtml $inline;

    public function __construct(string $postHtml)
    {
        $this->inline = new InlineHtml($postHtml);
    }

    protected function toHtml(): string
    {
        $body = $this->body($this->inline->toHtml());
        $limit = 0;
        $maxLength = 100;
        $html = '';
        /** @var \DOMNode $node */
        foreach ($body->childNodes as $node) {
            $volume = \mb_strLen($node->textContent);
            if ($limit + $volume < $maxLength) {
                $limit += $volume;
                $html .= $node->ownerDocument->saveHTML($node);
            } else {
                if ($node->nodeType === \XML_TEXT_NODE) {
                    $html .= \htmlSpecialChars(\mb_subStr($node->textContent, 0, $maxLength - $limit)) . '...';
                }
                break;
            }
        }
        return $html;
    }

    private function body(string $html): \DOMElement
    {
        $document = new \DOMDocument();
        \libxml_use_internal_errors(true);
        $document->loadHTML("<html><head><meta charset='utf-8'></head><body>$html</body></html>");
        return $document->getElementsByTagName('body')[0];
    }
}
