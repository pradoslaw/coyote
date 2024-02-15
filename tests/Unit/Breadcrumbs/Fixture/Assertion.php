<?php
namespace Tests\Unit\Breadcrumbs\Fixture;

use Tests\Unit\BaseFixture\View;
use Tests\Unit\BaseFixture\View\ViewDom;

trait Assertion
{
    use View\HtmlView;

    function breadcrumbs(string $uri): array
    {
        $breadcrumbs = [];
        $dom = new ViewDom($this->htmlView($uri));
        /** @var \DOMElement $breadcrumb */
        foreach ($dom->elements(xPath:"/html/body/footer//ul/li[@class='breadcrumb-item']/*") as $breadcrumb) {
            $name = \trim($breadcrumb->textContent);
            if (empty($name)) {
                continue;
            }
            $breadcrumbs[] = $this->breadcrumb($breadcrumb);
        }
        return $breadcrumbs;
    }

    function breadcrumb(\DOMElement $breadcrumb): Breadcrumb
    {
        $isAnchor = $breadcrumb->tagName === 'a';
        return new Breadcrumb(
            \trim($breadcrumb->textContent),
            $isAnchor,
            $this->hrefAttribute($breadcrumb));
    }

    function hrefAttribute(\DOMElement $breadcrumb): ?string
    {
        if ($breadcrumb->hasAttribute('href')) {
            return $breadcrumb->getAttribute('href');
        }
        return null;
    }
}
