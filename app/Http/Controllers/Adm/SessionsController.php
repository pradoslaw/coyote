<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Grids\Adm\SessionsGrid;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Boduch\Grid\Source\EloquentSource;

class SessionsController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function index(SessionRepository $session)
    {
        $this->breadcrumb->push('Kto jest online');

        $builder = $session->select(['sessions.*', 'users.name'])->leftJoin('users', 'users.id', '=', 'user_id');
        $grid = $this->gridBuilder()->createGrid(SessionsGrid::class)->setSource(new EloquentSource($builder));

        return $this->view('adm.sessions')->with('grid', $grid);
    }
}
