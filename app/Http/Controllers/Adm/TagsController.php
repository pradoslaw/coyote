<?php

namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Forms\TagForm;
use Coyote\Http\Grids\Adm\TagsGrid;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Tag;

class TagsController extends BaseController
{
    /**
     * @var TagRepository
     */
    private $tag;

    /**
     * @param TagRepository $tag
     */
    public function __construct(TagRepository $tag)
    {
        parent::__construct();

        $this->tag = $tag;
        $this->breadcrumb->push('Tagi', route('adm.tags'));
    }

    /**
     * @inheritdoc
     */
    public function index()
    {
        $builder = $this
            ->tag
            ->select(['tags.*', 'tag_categories.name AS category'])
            ->leftJoin('tag_categories', 'tag_categories.id', '=', 'category_id');

        $grid = $this->gridBuilder()
            ->createGrid(TagsGrid::class)
            ->setSource(new EloquentSource($builder));

        return $this->view('adm.tags.home')->with('grid', $grid);
    }

    /**
     * @param \Coyote\Tag $tag
     * @return \Illuminate\View\View
     */
    public function edit($tag)
    {
        $this->breadcrumb->push('Edycja');
        $form = $this->createForm(TagForm::class, $tag);

        return $this->view('adm.tags.save')->with('form', $form);
    }

    /**
     * @param \Coyote\Tag $tag
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($tag)
    {
        $form = $this->createForm(TagForm::class, $tag);
        $form->validate();

        $tag->fill($form->all());

        if ($form->getRequest()->hasFile('logo')) {
            $tag->logo->upload($form->getRequest()->file('logo'));
        }

        $tag->save();

        return redirect()->route('adm.tags')->with('success', 'Zmiany zostały zapisane.');
    }

    public function delete(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('adm.tags')->with('success', 'Tag został usunięty.');
    }
}
