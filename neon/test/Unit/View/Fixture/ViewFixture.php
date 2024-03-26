<?php
namespace Neon\Test\Unit\View\Fixture;

use Neon;
use Neon\Test\BaseFixture\View\ViewDom;

trait ViewFixture
{
    function view(array $fields): Neon\HtmlView
    {
        return new Neon\HtmlView([], [
            new Neon\View\Section(
                $fields['root'] ?? '',
                $fields['sectionTitle'] ?? '',
                []),
        ]);
    }

    function text(Neon\HtmlView $view, string $xPath): string
    {
        $dom = new ViewDom($view->html());
        return $dom->find($xPath);
    }

    function texts(Neon\HtmlView $view, string $xPath): array
    {
        $dom = new ViewDom($view->html());
        return $dom->findMany($xPath);
    }
}
