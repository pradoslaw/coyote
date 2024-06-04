<?php
namespace Coyote\Domain\Administrator\View;

use Coyote\Domain\Html;

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
        return $this->trimmed($this->textContent(), 85);
    }

    private function textContent(): string
    {
        return $this->firstParagraph($this->document($this->postHtml));
    }

    private function firstParagraph(\DOMDocument $document): string
    {
        $xPath = new \DOMXPath($document);
        foreach ($xPath->query('/html/body/p/text()') as $item) {
            return $item->ownerDocument->saveHTML($item);
        }
        return '';
    }

    private function document(string $html): \DOMDocument
    {
        $document = new \DOMDocument();
        \libxml_use_internal_errors(true);
        $document->loadHTML("<html><head><meta charset='utf-8'></head><body>$html</body></html>");
        return $document;
    }

    private function trimmed(string $string, int $length): string
    {
        if (\mb_strLen($string) > $length) {
            return \mb_subStr($string, 0, $length) . '...';
        }
        return $string;
    }
}
