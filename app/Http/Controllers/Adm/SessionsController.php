<?php
namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\CollectionSource;
use Coyote\Http\Grids\Adm\SessionsGrid;
use Coyote\Repositories\Redis\SessionRepository;
use Coyote\Services\Session\Registered;
use Illuminate\View\View;

class SessionsController extends BaseController
{
    public function index(SessionRepository $session, Registered $registered): View
    {
        $this->breadcrumb->push('Kto jest online', route('adm.sessions'));
        $grid = $this->gridBuilder()
            ->createGrid(SessionsGrid::class)
            ->setSource(new CollectionSource($registered->setup($session->all())));
        return $this->view('adm.sessions')->with('grid', $grid);
    }
}
