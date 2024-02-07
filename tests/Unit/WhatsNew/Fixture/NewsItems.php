<?php
namespace Tests\Unit\WhatsNew\Fixture;

use Tests\Unit\BaseFixture\View;
use Tests\Unit\BaseFixture\View\ViewDom;

trait NewsItems
{
    use View\HtmlView;

    function newsItem(): array
    {
        $dom = new ViewDom($this->htmlView('/'));
        /** @var \DOMElement $listItem */
        foreach ($dom->elements(xPath:'//aside//div[@class="card bg-dark"]//ul/li') as $listItem) {
            return $this->listItem($listItem);
        }
        throw new \AssertionError("Failed finding news item.");
    }

    function listItem(\DOMElement $listItem): array
    {
        $item = [];
        /** @var \DOMElement $child */
        foreach ($listItem->childNodes as $child) {
            if ($child->nodeName === 'a') {
                $item['text'] = $child->textContent;
                $item['href'] = $child->getAttribute('href');
            }
            if ($child->nodeName === 'div') {
                $item['date'] = $child->textContent;
            }
        }
        return $item;
    }
}
