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

    public function html(string $xPath): string
    {
        return $this->document->saveHTML($this->first($this->query($xPath), $xPath));
    }

    public function collectionHtml(string $xPath): array
    {
        $html = [];
        foreach ($this->query($xPath) as $element) {
            $html[] = \trim($this->document->saveHTML($element));
        }
        return $html;
    }

    public function innerHtml(string $xPath): string
    {
        /** @var \DOMElement $node */
        $node = $this->first($this->query($xPath), $xPath);
        return \trim(
            \implode('',
                \array_map(
                    $this->document->saveHTML(...),
                    \iterator_to_array($node->childNodes))));
    }

    public function exists(string $xPath): bool
    {
        return $this->query($xPath)->count() > 0;
    }

    public function findStrings(string $xPath): array
    {
        $texts = [];
        foreach ($this->query($xPath) as $child) {
            $texts[] = $this->string($child);
        }
        return $texts;
    }

    public function findString(string $xPath): string
    {
        return $this->string($this->first($this->query($xPath), $xPath));
    }

    private function query(string $xPath): \DOMNodeList
    {
        $result = $this->xPath->query($xPath);
        if ($result === false) {
            throw new \Exception("Failed to execute malformed xPath: $xPath");
        }
        return $result;
    }

    private function string(\DOMText|\DOMElement|\DOMAttr $node): string
    {
        if ($node->nodeType === \XML_ATTRIBUTE_NODE) {
            return $node->textContent;
        }
        if ($node->nodeType === \XML_TEXT_NODE) {
            return $node->textContent;
        }
        throw new \Exception("Failed to get element as string: <$node->tagName>");
    }

    private function first(\DOMNodeList $nodes, string $xPath): \DOMElement|\DOMText|\DOMAttr
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
        return new \Exception("$summary: $xPath");
    }

    public function docType(): string
    {
        $node = $this->document->getRootNode();
        return \strStr($this->document->saveHTML($node), "\n", true);
    }
}
