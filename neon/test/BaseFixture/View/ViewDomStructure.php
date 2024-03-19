<?php
namespace Neon\Test\BaseFixture\View;

readonly class ViewDomStructure
{
    public function __construct(private \DOMDocument $document)
    {
    }

    public function structure(): string
    {
        return $this->elementAsString($this->htmlNode());
    }

    private function htmlNode(): \DOMElement
    {
        return $this->document->getElementsByTagName('html')[0];
    }

    private function elementAsString(\DOMElement $element): string
    {
        $childNames = $this->childrenAsStrings($element);
        if (empty($childNames)) {
            return $element->nodeName;
        }
        $children = \implode(',', $childNames);
        return "{$element->nodeName}($children)";
    }

    /**
     * @return string[]
     */
    private function childrenAsStrings(\DOMElement $element): array
    {
        $childNames = [];
        foreach ($element->childNodes as $child) {
            /** @var \DOMNode $child */
            if ($child->nodeType === \XML_ELEMENT_NODE) {
                $childNames[] = $this->elementAsString($child);
            }
        }
        return $childNames;
    }
}
