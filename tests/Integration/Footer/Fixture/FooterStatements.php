<?php
namespace Tests\Integration\Footer\Fixture;

use Tests\Integration\BaseFixture\View;
use Tests\Integration\BaseFixture\View\ViewDom;

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
