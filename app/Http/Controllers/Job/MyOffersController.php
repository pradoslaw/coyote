<?php

namespace Coyote\Http\Controllers\Job;

use Boduch\Grid\Source\CollectionSource;
use Coyote\Http\Grids\Job\MyOffersGrid;

class MyOffersController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $this->tab = 'my_offers';

        $offers = $this->job->getMyOffers($this->userId);
        $grid = $this->gridBuilder()->createGrid(MyOffersGrid::class)->setSource(new CollectionSource($offers));

        return $this->view('job.my_offers')->with('grid', $grid);
    }
}
