<?php
namespace Tests\Legacy\IntegrationNew\Footer\Fixture;

use Tests\Legacy\IntegrationNew\BaseFixture\View;
use Tests\Legacy\IntegrationNew\BaseFixture\View\ViewDom;

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
