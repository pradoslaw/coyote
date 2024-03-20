<?php
namespace Neon\Test\BaseFixture\View;

readonly class ViewDom
{
    private \DOMDocument $document;
    private \DOMXPath $xPath;

    public function __construct(private string $html)
    {
        $this->document = new \DOMDocument();
        \libxml_use_internal_errors(true);
        $this->document->loadHTML($this->html);
        \libxml_clear_errors();
        $this->xPath = new \DOMXPath($this->document);
    }

    public function findMany(string $xPath): array
    {
        $texts = [];
        foreach ($this->xPath->query($xPath) as $child) {
            $texts[] = $this->text($child);
        }
        return $texts;
    }

    public function find(string $xPath): string
    {
        return $this->text($this->first($this->xPath->query($xPath), $xPath));
    }

    private function text(\DOMText|\DOMElement $node): string
    {
        if ($node->nodeType === \XML_TEXT_NODE) {
            return $node->textContent;
        }
        $tagName = $node->tagName;
        throw new \Exception("Failed to get text of element: <$tagName>");
    }

    private function first(\DOMNodeList $nodes, string $xPath): \DOMElement|\DOMText
    {
        $count = $nodes->count();
        if ($count === 0) {
            throw $this->exception('Failed to find element', $xPath);
        }
        if ($count === 1) {
            return $nodes[0];
        }
        throw $this->exception("Failed to find unique element (found $count)", $xPath);
    }

    private function exception(string $summary, string $xPath): \Exception
    {
        $structure = new ViewDomStructure($this->document);
        return new \Exception("$summary: $xPath\n\n" . $structure->structure());
    }

    public function docType(): string
    {
        $node = $this->document->getRootNode();
        return \strStr($this->document->saveHTML($node), "\n", true);
    }
}
