<?php
namespace Coyote\Domain\Administrator\View;

use Coyote\Domain\Html;

class SubstringHtml extends Html
{
    private InlineHtml $inline;

    public function __construct(Html $html, private int $length)
    {
        $this->inline = new InlineHtml($html);
    }

    protected function toHtml(): string
    {
        $body = $this->body($this->inline->toHtml());
        $limit = 0;
        $html = '';
        /** @var \DOMNode $node */
        foreach ($body->childNodes as $node) {
            $volume = \mb_strLen($node->textContent);
            if ($limit + $volume < $this->length) {
                $limit += $volume;
                $html .= $node->ownerDocument->saveHTML($node);
            } else {
                if ($node->nodeType === \XML_TEXT_NODE) {
                    $html .= \htmlSpecialChars(\mb_subStr($node->textContent, 0, $this->length - $limit)) . '...';
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
