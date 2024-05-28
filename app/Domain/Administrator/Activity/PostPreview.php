<?php
namespace Coyote\Domain\Administrator\Activity;

use Coyote\View\Twig\TwigLiteral;
use DOMElement;

class PostPreview
{
    public function __construct(private string $contentMarkdown)
    {
    }

    public function hasPreview(): bool
    {
        return \str_contains($this->contentMarkdown, "\n");
    }

    public function previewHtml(): ?TwigLiteral
    {
        $content = $this->previewString($this->htmlString());
        if ($content) {
            return new TwigLiteral($content);
        }
        return null;
    }

    public function html(): TwigLiteral
    {
        return new TwigLiteral($this->htmlString());
    }

    private function htmlString(): string
    {
        return app('parser.post')->parse($this->contentMarkdown);
    }

    private function previewString(string $html): ?string
    {
        if ($html === '') {
            return '';
        }
        $document = new \DOMDocument();
        $document->loadHTML("<html><head><meta charset='utf-8'></head><body>$html</body></html>");
        return $this->htmlWithoutBlockQuotes($document);
    }

    private function htmlWithoutBlockQuotes(\DOMDocument $document): ?string
    {
        $xPath = new \DOMXPath($document);
        /** @var DOMElement $item */
        foreach ($xPath->query('/html/body/*') as $item) {
            if ($item->tagName === 'p') {
                return $this->toHtml($item);
            }
        }
        return null;
    }

    private function toHtml(DOMElement $element): string
    {
        return $element->ownerDocument->saveHTML($element);
    }
}
