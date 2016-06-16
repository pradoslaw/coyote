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
     * @param Request $request
     * @param WikiRepository $wiki
     */
    public function __construct(Request $request, WikiRepository $wiki)
    {
        parent::__construct();

        $this->wiki = $wiki;
        $this->buildBreadcrumb($request->wiki);
    }

    /**
     * @param \Coyote\Wiki $wiki
     */
    protected function buildBreadcrumb($wiki)
    {
        if (!empty($wiki)) {
            $this->parents = $this->wiki->parents($wiki->path_id);

            $this->parents->reverse()->each(function ($item) {
                /** @var \Coyote\Wiki $item */
                $this->breadcrumb->push($item->title, url($item->path));
            });
        }
    }

    /**
     * @return \Coyote\Services\Parser\Parsers\ParserInterface
     */
    protected function getParser()
    {
        return app('parser.wiki');
    }
}
