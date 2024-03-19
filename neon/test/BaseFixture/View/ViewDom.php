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

    public function find(string $xPath): string
    {
        $first = $this->first($this->xPath->query($xPath), $xPath);
        return $first->textContent;
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
