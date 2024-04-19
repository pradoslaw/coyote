<?php
namespace Neon\Test\BaseFixture;

use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View\Html\Item;
use Neon\View\HtmlView;

class ItemView
{
    private ViewDom $viewDom;

    public function __construct(Item $item)
    {
        $view = new HtmlView([], [$item]);
        $this->viewDom = new ViewDom($view->html());
    }

    public function find(string ...$selectors): string
    {
        $selector = new Selector(...$selectors);
        return $this->viewDom->findString($selector->xPath());
    }

    /**
     * @return string[]
     */
    public function findMany(string ...$selectors): array
    {
        $selector = new Selector(...$selectors);
        return $this->viewDom->findStrings($selector->xPath());
    }

    public function findText(string ...$selectors): string
    {
        $selector = new Selector(...\array_merge($selectors, ['text()']));
        return $this->viewDom->findString($selector->xPath());
    }

    /**
     * @return string[]
     */
    public function findTextMany(string ...$selectors): array
    {
        $selector = new Selector(...\array_merge($selectors, ['text()']));
        return $this->viewDom->findStrings($selector->xPath());
    }

    public function exists(string ...$selectors): bool
    {
        $selector = new Selector(...$selectors);
        return $this->viewDom->exists($selector->xPath());
    }

    public function cssClasses(string ...$selectors): array
    {
        $classAttribute = $this->find(...\array_merge($selectors, ['@class']));
        return \explode(' ', $classAttribute);
    }
}
