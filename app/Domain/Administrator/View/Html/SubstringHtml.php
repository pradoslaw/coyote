<?php
namespace Coyote\Domain\Administrator\View\Html;

use Coyote\Domain\Html;

class SubstringHtml extends Html
{
    public function __construct(private Html $html, private int $limit)
    {
    }

    protected function toHtml(): string
    {
        $body = $this->body($this->html->toHtml());
        return $this->substringTo($body, $this->limit);
    }

    private function substringTo(\DOMElement $body, int $limit): string
    {
        $length = 0;
        $html = '';
        /** @var \DOMNode $node */
        foreach ($body->childNodes as $node) {
            $volume = \mb_strLen($node->textContent);
            if ($length + $volume < $limit) {
                $length += $volume;
                $html .= $node->ownerDocument->saveHTML($node);
            } else {
                if ($node->nodeType === \XML_TEXT_NODE) {
                    /** @var \DOMText $node */
                    $html .= \htmlSpecialChars(\mb_subStr($node->textContent, 0, $limit - $length)) . '...';
                }
                if ($node->nodeType === \XML_ELEMENT_NODE) {
                    /** @var \DOMElement $node */
                    $contentHtml = $this->substringTo($node, $limit - $length);
                    $html .= "<$node->tagName>$contentHtml</{$node->tagName}>";
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
