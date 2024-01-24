<?php
namespace Tests\Unit\BaseFixture;

class ViewFixture
{
    private ViewDom $dom;

    public function __construct(string $html)
    {
        $this->dom = new ViewDom($html);
    }

    /**
     * @return Script[]
     */
    public function javaScriptDeclarations(): array
    {
        $declarations = [];
        foreach ($this->scriptTags() as $script) {
            if (!empty($script->content())) {
                $declarations[] = $script;
            }
        }
        return $declarations;
    }

    private function scriptTags(): iterable
    {
        foreach ($this->dom->elements('/html/body//script') as $scriptElement) {
            yield new Script($scriptElement);
        }
    }

    public function metaDeclarations(): array
    {
        $declarations = [];

        /** @var \DOMElement $element */
        foreach ($this->dom->elements(xPath:'/html/head/meta') as $element) {
            $declarations[] = [
                'property' => $this->attribute($element, 'property'),
                'name'     => $this->attribute($element, 'name'),
                'content'  => $this->attribute($element, 'content'),
            ];
        }
        return $declarations;
    }

    private function attribute(\DOMElement $element, string $attribute): ?string
    {
        if ($element->hasAttribute($attribute)) {
            return $element->getAttribute($attribute);
        }
        return null;
    }
}
