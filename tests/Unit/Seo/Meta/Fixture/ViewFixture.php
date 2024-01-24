<?php
namespace Tests\Unit\Seo\Meta\Fixture;

use Tests\Unit\BaseFixture\ViewDom;

class ViewFixture
{
    private ViewDom $dom;

    public function __construct(string $html)
    {
        $this->dom = new ViewDom($html);
    }

    public function metaProperty(string $name): string
    {
        /** @var \DOMElement $element */
        foreach ($this->dom->elements(xPath:'/html/head/meta') as $element) {
            if ($element->getAttribute('name') === $name) {
                return $element->getAttribute('content');
            }
        }
        throw new \Exception("Failed to recognize in view meta property: $name");
    }
}
