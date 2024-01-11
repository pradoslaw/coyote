<?php
namespace Tests\Unit\Seo\Fixture;

use Tests\Unit\BaseFixture\ViewDom;

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
}
