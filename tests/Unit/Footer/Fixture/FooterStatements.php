<?php
namespace Tests\Unit\Footer\Fixture;

use Tests\Unit\BaseFixture\View;
use Tests\Unit\BaseFixture\View\ViewDom;

trait FooterStatements
{
    use View\HtmlView;

    function footerStatements(): array
    {
        $dom = new ViewDom($this->htmlView('/'));
        $texts = [];
        /** @var \DOMText $node */
        foreach ($dom->elements(xPath:'/html/body/footer/div[@id="footer-copyright"]//text()[normalize-space()]') as $node) {
            $texts[] = \trim($node->textContent);
        }
        return $texts;
    }
}
