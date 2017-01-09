<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;

abstract class BaseController extends Controller
{
    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @var \Coyote\Wiki[]
     */
    protected $parents;

    /**
     * @param WikiRepository $wiki
     */
    public function __construct(WikiRepository $wiki)
    {
        parent::__construct();

        $this->wiki = $wiki;

        $this->middleware(function (Request $request, $next) {
            $this->buildBreadcrumb($request->attributes->get('wiki'));

            return $next($request);
        });
    }

    /**
     * @param \Coyote\Wiki $wiki
     */
    protected function buildBreadcrumb($wiki)
    {
        if (!empty($wiki)) {
            $this->parents = $this->wiki->parents($wiki->id);

            $this->parents->reverse()->each(function ($item) {
                /** @var \Coyote\Wiki $item */
                $this->breadcrumb->push($item->title, url($item->path));
            });
        }
    }

    /**
     * @return \Coyote\Services\Parser\Factories\AbstractFactory
     */
    protected function getParser()
    {
        return app('parser.wiki');
    }
}
