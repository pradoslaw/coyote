<?php
namespace Neon\Test\Unit\View\Fixture;

use Neon;
use Neon\Test\BaseFixture\View\ViewDom;

trait ViewFixture
{
    function view(string $title): Neon\View
    {
        return new Neon\View($title);
    }

    function texts(Neon\View $view, string $xPath): array
    {
        $dom = new ViewDom($view->html());
        return $dom->findMany($xPath);
    }
}
