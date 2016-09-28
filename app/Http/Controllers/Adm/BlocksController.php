<?php

namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentDataSource;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Forms\BlockForm;
use Coyote\Http\Grids\Adm\BlockGrid;
use Coyote\Repositories\Contracts\BlockRepositoryInterface as BlockRepository;
use Coyote\Services\Stream\Objects\Block as Stream_Block;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;

class BlocksController extends BaseController
{
    use CacheFactory;

    /**
     * @var BlockRepository
     */
    private $block;

    /**
     * @param BlockRepository $block
     */
    public function __construct(BlockRepository $block)
    {
        parent::__construct();

        $this->block = $block;
        $this->breadcrumb->push('Bloki statyczne', route('adm.blocks'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $grid = $this->gridBuilder()->createGrid(BlockGrid::class)->setSource(new EloquentDataSource($this->block));

        return $this->view('adm.blocks.home')->with('grid', $grid);
    }

    /**
     * @param \Coyote\Block $block
     * @return \Illuminate\View\View
     */
    public function edit($block)
    {
        $this->breadcrumb->push('Edycja');
        $form = $this->getForm($block);

        return $this->view('adm.blocks.save', ['form' => $form]);
    }

    /**
     * @param \Coyote\Block $block
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($block)
    {
        $form = $this->getForm($block);
        $form->validate();

        $block->fill($form->all());

        $this->transaction(function () use ($block) {
            $block->save();

            stream(
                $block->wasRecentlyCreated ? Stream_Create::class : Stream_Update::class,
                (new Stream_Block())->map($block)
            );
            $this->getCacheFactory()->forget('block:' . $block->name);
        });

        return redirect()->route('adm.blocks')->with('success', 'Zmiany w bloku zostały zapisane.');
    }

    /**
     * @param \Coyote\Block $block
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($block)
    {
        $this->transaction(function () use ($block) {
            $block->delete();

            stream(Stream_Delete::class, (new Stream_Block())->map($block));
            $this->getCacheFactory()->forget('block:' . $block->name);
        });

        return redirect()->route('adm.blocks')->with('success', 'Block został prawidłowo usunięty.');
    }

    /**
     * @param \Coyote\Block $block
     * @return \Coyote\Services\FormBuilder\Form
     */
    private function getForm($block)
    {
        return $this->createForm(BlockForm::class, $block);
    }
}
