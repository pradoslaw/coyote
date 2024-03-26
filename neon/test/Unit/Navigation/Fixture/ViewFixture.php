<?php
namespace Neon\Test\Unit\Navigation\Fixture;

use Neon;
use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View\Html\Navigation;

trait ViewFixture
{
    function navigationView(array $fields): Neon\View\HtmlView
    {
        return new Neon\View\HtmlView([], [
            new Navigation($this->viewModel($fields)),
        ]);
    }

    function text(Neon\View\HtmlView $view, Selector $selector): string
    {
        $dom = new ViewDom($view->html());
        return $dom->find($selector->xPath());
    }

    function texts(Neon\View\HtmlView $view, Selector $selector): array
    {
        $dom = new ViewDom($view->html());
        return $dom->findMany($selector->xPath());
    }

    function viewModel(array $fields): Neon\View\ViewModel\Navigation
    {
        return new Neon\View\ViewModel\Navigation(
            $fields['items'] ?? [],
            $fields['githubUrl'] ?? '',
            $fields['githubStarsUrl'] ?? '',
            $fields['githubName'] ?? '',
            $fields['githubStars'] ?? -1,
            $fields['controls'] ?? [],
        );
    }
}
