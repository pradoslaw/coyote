<?php

namespace Coyote\Http\Controllers\Adm\Forum;

use Coyote\Http\Controllers\Adm\BaseController;
use Coyote\Http\Grids\Adm\Forum\CategoriesGrid;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Services\Grid\Source\EloquentDataSource;

class CategoriesController extends BaseController
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @param ForumRepository $forum
     */
    public function __construct(ForumRepository $forum)
    {
        parent::__construct();

        $this->forum = $forum;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Forum', route('adm.forum.categories'));

        $grid = $this->getGrid()->createGrid(CategoriesGrid::class);
        $grid->setSource(new EloquentDataSource($this->forum));

        return $this->view('adm.forum.categories.home')->with('grid', $grid);
    }
}
