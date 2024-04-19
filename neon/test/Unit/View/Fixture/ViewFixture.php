<?php
namespace Neon\Test\Unit\View\Fixture;

use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View\View;

trait ViewFixture
{
    function viewSectionTitle(View $view): string
    {
        return $this->dom($view)->findText('//main//h1/text()');
    }

    function viewSubsectionTitle(View $view): string
    {
        return $this->dom($view)->findText('//main//h2/text()');
    }

    function viewSectionBreadcrumbs(View $view): array
    {
        return $this->dom($view)->findTextMany('/html/body//nav/ul/li/text()');
    }

    function viewNavigationItems(View $view): array
    {
        return $this->findTextMany($view, 'nav', 'ul.menu-items', 'li', 'a');
    }

    function viewHeaderControls(View $view): array
    {
        return $this->findTextMany($view, 'ul.controls', 'li', 'a');
    }

    function findTextMany(View $view, string...$selectors): array
    {
        $selector = new Selector(...\array_merge($selectors, ['text()']));
        return $this->dom($view)->findTextMany($selector->xPath());
    }

    function findMany(View $view, string...$selectors): array
    {
        $selector = new Selector(...$selectors);
        return $this->dom($view)->findTextMany($selector->xPath());
    }

    function dom(View $view): ViewDom
    {
        return new ViewDom($view->html());
    }
}
