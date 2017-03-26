<?php

namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\CollectionSource;
use Coyote\Http\Grids\Adm\SessionsGrid;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Coyote\Services\Session\Registered;

class SessionsController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function index(SessionRepository $session, Registered $registered)
    {
        $this->breadcrumb->push('Kto jest online', route('adm.sessions'));

        $grid = $this->gridBuilder()
            ->createGrid(SessionsGrid::class)
            ->setSource(new CollectionSource($this->collect($registered->setup(collect($session->all())))));

        return $this->view('adm.sessions')->with('grid', $grid);
    }

    private function collect($collection)
    {
        foreach ($collection as $key => $value) {
            $collection[$key] = collect($value);
        }

        return $collection;
    }
}
