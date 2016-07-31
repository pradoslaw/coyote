<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Grids\Adm\FlagsGrid;
use Coyote\Repositories\Contracts\FlagRepositoryInterface as FlagRepository;
use Coyote\Repositories\Criteria\FlagList;
use Coyote\Services\Grid\Source\EloquentDataSource;

class FlagController extends BaseController
{
    /**
     * @var FlagRepository
     */
    protected $flag;

    /**
     * @param FlagRepository $flag
     */
    public function __construct(FlagRepository $flag)
    {
        parent::__construct();

        $this->flag = $flag;
        $this->breadcrumb->push('Raporty', route('adm.flag'));
    }

    /**
     * @inheritdoc
     */
    public function index()
    {
        $this->flag->pushCriteria(new FlagList());
        $this->flag->applyCriteria();

        $grid = $this->getGridBuilder()->createGrid(FlagsGrid::class);
        $grid->setSource(new EloquentDataSource($this->flag));

        return $this->view('adm.flag')->with('grid', $grid);
    }
}
