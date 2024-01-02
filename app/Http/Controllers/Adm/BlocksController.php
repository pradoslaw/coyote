<?php

namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Block;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Forms\BlockForm;
use Coyote\Http\Grids\Adm\BlockGrid;
use Coyote\Repositories\Eloquent\BlockRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Block as Stream_Block;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlocksController extends BaseController
{
    use CacheFactory;

    public function __construct(private BlockRepository $block)
    {
        parent::__construct();
        $this->breadcrumb->push('Bloki statyczne', route('adm.blocks'));
    }

    public function index(): View
    {
        $grid = $this->gridBuilder()->createGrid(BlockGrid::class)->setSource(new EloquentSource($this->block));
        return $this->view('adm.blocks.home')->with('grid', $grid);
    }

    public function edit(Block $block): View
    {
        $this->breadcrumb->push('Edycja', route('adm.blocks.save', ['block' => $block]));
        $form = $this->getForm($block);
        return $this->view('adm.blocks.save', ['form' => $form]);
    }

    public function save(Block $block): RedirectResponse
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
            $this->flushCache();
        });

        return redirect()->route('adm.blocks')->with('success', 'Zmiany w bloku zostały zapisane.');
    }

    public function delete(Block $block): RedirectResponse
    {
        $this->transaction(function () use ($block) {
            $block->delete();
            stream(Stream_Delete::class, (new Stream_Block())->map($block));
            $this->flushCache();
        });
        return redirect()->route('adm.blocks')->with('success', 'Block został prawidłowo usunięty.');
    }

    private function getForm(Block $block): Form
    {
        return $this->createForm(BlockForm::class, $block);
    }

    /**
     * Clear users cache permission after updating groups etc.
     */
    protected function flushCache()
    {
        $this->getCacheFactory()->forget('blocks');
    }
}
