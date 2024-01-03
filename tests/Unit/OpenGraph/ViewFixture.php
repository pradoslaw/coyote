<?php
namespace Tests\Unit\OpenGraph;

use DOMDocument;
use DOMXPath;

class ViewFixture
{
    private DOMDocument $document;

    public function __construct(private string $html)
    {
        $this->document = new DOMDocument();
        @$this->document->loadHTML($this->html);
    }

    public function metaProperty(string $property): string
    {
        /** @var \DOMElement $element */
        foreach ($this->elements(xPath:'/html/head/meta') as $element) {
            if ($element->getAttribute('property') === $property) {
                return $element->getAttribute('content');
            }
        }
        throw new \Exception("Failed to recognize in view meta property: $property");
    }

    private function elements(string $xPath): iterable
    {
        $path = new DomXPath($this->document);
        return $path->query($xPath);
    }
}
