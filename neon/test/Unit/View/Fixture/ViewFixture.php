<?php
namespace Neon\Test\Unit\View\Fixture;

use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View\View;

trait ViewFixture
{
    function viewSectionTitle(View $view): string
    {
        return $this->dom($view)->find('//main//h1/text()');
    }

    function viewSubsectionTitle(View $view): string
    {
        return $this->dom($view)->find('//main//h2/text()');
    }

    function viewSectionBreadcrumbs(View $view): array
    {
        return $this->dom($view)->findMany('/html/body//nav/ul/li/text()');
    }

    function viewNavigationItems(View $view): array
    {
        return $this->findMany($view, 'nav', 'ul.menu-items', 'li', 'a');
    }

    function viewHeaderControls(View $view): array
    {
        return $this->findMany($view, 'ul.controls', 'li', 'a');
    }

    function findMany(View $view, string...$selectors): array
    {
        $selector = new Selector(...$selectors);
        return $this->dom($view)->findMany($selector->xPath());
    }

    function dom(View $view): ViewDom
    {
        return new ViewDom($view->html());
    }
}
