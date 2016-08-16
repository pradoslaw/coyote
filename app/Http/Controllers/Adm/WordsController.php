<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Grids\Adm\WordsGrid;
use Coyote\Repositories\Contracts\WordRepositoryInterface as WordRepository;
use Coyote\Services\Grid\Source\EloquentDataSource;
use Illuminate\Http\Request;

class WordsController extends BaseController
{
    /**
     * @var WordRepository
     */
    protected $word;

    /**
     * @param WordRepository $word
     */
    public function __construct(WordRepository $word)
    {
        parent::__construct();

        $this->word = $word;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Cenzura', route('adm.words'));

        $grid = $this
            ->getGridBuilder()
            ->createGrid(WordsGrid::class)
            ->setSource(new EloquentDataSource($this->word))
            ->setEnablePagination(false);

        return $this->view('adm.words')->with('grid', $grid);
    }

    public function save(Request $request)
    {
        //
    }
}
