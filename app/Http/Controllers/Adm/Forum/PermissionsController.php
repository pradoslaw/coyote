<?php

namespace Coyote\Http\Controllers\Adm\Forum;

use Coyote\Http\Controllers\Adm\BaseController;
use Coyote\Http\Grids\Adm\Forum\PermissionsGrid;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;

class PermissionsController extends BaseController
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $grid = $this->getGrid()->createGrid(PermissionsGrid::class);

        return $this->view('adm.forum.permissions.home')->with('grid', $grid);
    }

    public function edit()
    {
        //
    }
}
