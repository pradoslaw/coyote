<?php
namespace Coyote\Domain\Administrator\View;

use Coyote\Domain\Html;
use DOMElement;

class PostPreview extends Html
{
    public function __construct(private string $postHtml)
    {
    }

    protected function toHtml(): string
    {
        if ($this->postHtml === '') {
            return '';
        }
        return $this->firstParagraph($this->document($this->postHtml));
    }

    private function firstParagraph(\DOMDocument $document): string
    {
        $xPath = new \DOMXPath($document);
        /** @var DOMElement $item */
        foreach ($xPath->query('/html/body/*') as $item) {
            if ($item->tagName === 'p') {
                return $this->elementToHtml($item);
            }
        }
        return '';
    }

    private function elementToHtml(DOMElement $element): string
    {
        return $element->ownerDocument->saveHTML($element);
    }

    private function document(string $html): \DOMDocument
    {
        $document = new \DOMDocument();
        $document->loadHTML("<html><head><meta charset='utf-8'></head><body>$html</body></html>");
        return $document;
    }
}
