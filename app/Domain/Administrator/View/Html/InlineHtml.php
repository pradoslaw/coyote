<?php
namespace Coyote\Domain\Administrator\View\Html;

use Coyote\Domain\Html;
use Coyote\Domain\Icon\Icons;

class InlineHtml extends Html
{
    private ?\DOMElement $body;

    public function __construct(string $postHtml)
    {
        if ($postHtml === '') {
            $this->body = null;
        } else {
            $this->body = $this->body($postHtml);
        }
    }

    protected function toHtml(): string
    {
        if ($this->body === null) {
            return '';
        }
        return $this->lineString($this->unwrapChildren($this->body));
    }

    private function lineString(string $items): string
    {
        return \trim(\str_replace("\n", ' ', $items));
    }

    private function unwrapChildren(\DOMElement $element): string
    {
        return \implode('',
            \array_map(
                $this->unwrapNode(...),
                \iterator_to_array($element->childNodes)));
    }

    private function unwrapNode(\DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return $node->ownerDocument->saveHTML($node);
        }
        /** @var \DOMElement $node */
        return $this->unwrapElement($node);
    }

    private function unwrapElement(\DOMElement $item): string
    {
        if (\in_array($item->tagName, ['span', 'ul', 'ol'])) {
            return $this->unwrapChildren($item);
        }
        if ($item->tagName === 'blockquote') {
            return $this->badge('contentMarkerQuote');
        }
        if ($item->tagName === 'video') {
            return $this->badge('contentMarkerVideo', 'video');
        }
        if ($item->tagName === 'table') {
            return $this->badge('contentMarkerTable', 'table');
        }
        if ($item->tagName === 'iframe') {
            return $this->badge('contentMarkerIFrame', 'iframe');
        }
        if ($item->getAttribute('class') === 'markdown-code') {
            return $this->badge('contentMarkerCode', 'code');
        }
        if ($item->tagName === 'br') {
            return ' ' . $this->lineBreak();
        }
        if ($item->tagName === 'img') {
            if ($item->getAttribute('class') !== 'img-smile') {
                return $this->badge('contentMarkerImage', 'image');
            }
        }
        $children = $this->unwrapChildren($item);
        if ($item->tagName === 'a') {
            if ($item->getAttribute('class') === 'mention') {
                return '<span class="fake-anchor fake-mention">' . $children . '</span>';
            }
            return '<span class="fake-anchor">' . $children . '</span>';
        }
        if ($item->tagName === 'li') {
            return '• ' . $children;
        }
        $headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        if (\in_array($item->tagName, $headings)) {
            return $this->badge('contentMarkerHeading') . $children;
        }
        if ($item->tagName === 'p') {
            if ($item->previousElementSibling && \in_array($item->previousElementSibling->tagName, ['p', ...$headings])) {
                return $this->lineBreak() . ' ' . $children;
            }
            return $children;
        }
        return $item->ownerDocument->saveHTML($item);
    }

    private function badge(string $iconName, string $title = ''): string
    {
        $icon = (new Icons)->icon($iconName);
        if ($title) {
            $titleHtml = '<span class="ms-1">' . $title . '</span>';
        } else {
            $titleHtml = '';
        }
        return '<span class="badge badge-material-element">' . $icon . $titleHtml . '</span>';
    }

    private function body(string $html): \DOMElement
    {
        $document = new \DOMDocument();
        \libxml_use_internal_errors(true);
        $document->loadHTML("<html><head><meta charset='utf-8'></head><body>$html</body></html>");
        return $document->getElementsByTagName('body')[0];
    }

    private function lineBreak(): string
    {
        return '↵';
    }
}
