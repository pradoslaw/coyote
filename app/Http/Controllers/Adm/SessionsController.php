<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Grids\Adm\SessionsGrid;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Boduch\Grid\Source\EloquentDataSource;

class SessionsController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function index(SessionRepository $session)
    {
        $this->breadcrumb->push('Kto jest online');

        $builder = $session->select(['sessions.*', 'users.name'])->leftJoin('users', 'users.id', '=', 'user_id');
        $grid = $this->getGridBuilder()->createGrid(SessionsGrid::class)->setSource(new EloquentDataSource($builder));

        return $this->view('adm.sessions')->with('grid', $grid);
    }
}
