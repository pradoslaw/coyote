<?php
namespace Coyote\Domain\Administrator\Activity;

use Carbon\Carbon;
use Coyote\View\Twig\TwigLiteral;
use DOMElement;

class Post
{
    public function __construct(
        public string  $text,
        public string  $forumName,
        public string  $forumUrl,
        public string  $topicTitle,
        public string  $postUrl,
        private Carbon $createdAt,
        public bool    $deleted,
        public bool    $isThread,
    )
    {
    }

    public function html(): TwigLiteral
    {
        return new TwigLiteral($this->htmlString());
    }

    private function htmlString()
    {
        return app('parser.post')->parse($this->text);
    }

    public function isLong(): bool
    {
        return \str_contains($this->text, "\n");
    }

    public function dateString(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    public function previewHtml(): ?TwigLiteral
    {
        $content = $this->previewString($this->htmlString());
        if ($content) {
            return new TwigLiteral($content);
        }
        return null;
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
