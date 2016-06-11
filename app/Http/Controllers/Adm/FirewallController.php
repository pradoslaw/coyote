<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Grids\Adm\FirewallGrid;
use Coyote\Repositories\Contracts\FirewallRepositoryInterface as FirewallRepository;
use Coyote\Repositories\Criteria\FirewallList;
use Coyote\Services\Grid\Source\EloquentDataSource;

class FirewallController extends BaseController
{
    /**
     * @var FirewallRepository
     */
    private $firewall;

    /**
     * @param FirewallRepository $firewall
     */
    public function __construct(FirewallRepository $firewall)
    {
        parent::__construct();

        $this->firewall = $firewall;
        $this->breadcrumb->push('Bany', route('adm.firewall'));
    }

    /**
     * @inheritdoc
     */
    public function index()
    {
        $this->firewall->pushCriteria(new FirewallList());
        $this->firewall->applyCriteria();

        $grid = $this->getGrid()->createGrid(FirewallGrid::class);
        $grid->setSource(new EloquentDataSource($this->firewall));

        return $this->view('adm.firewall.home', ['grid' => $grid]);
    }
}
