<?php

namespace Coyote\Http\Controllers\Wiki;

use Boduch\Grid\Source\EloquentDataSource;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Grids\Wiki\LogGrid;
use Coyote\Repositories\Criteria\Wiki\BelowDepth;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    use CacheFactory;

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->breadcrumb->push('Kompendium', route('wiki.home'));

        $cache = $this->getCacheFactory();

        if (!$cache->has('wiki:log') || $request->getQueryString() !== null) {
            $grid = $this->grid();

            if ($request->getQueryString() !== null) {
                $cache->put('wiki:log', $grid, 30);
            }
        } else {
            $grid = $cache->get('wiki:log');
        }

        $categories = $cache->remember('wiki:categories', 60 * 24, function () {
            $this->wiki->pushCriteria(new BelowDepth());
            return $this->wiki->children();
        });

        return $this->view('wiki.home')->with(compact('grid', 'categories'));
    }

    /**
     * @return string
     */
    private function grid()
    {
        return (string) $this
            ->gridBuilder()
            ->createGrid(LogGrid::class)
            ->setSource(new EloquentDataSource($this->wiki->getLogQuery()))
            ->render();
    }
}
