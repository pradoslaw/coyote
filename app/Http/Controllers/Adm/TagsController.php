<?php
namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Forms\TagForm;
use Coyote\Http\Grids\Adm\TagsGrid;
use Coyote\Repositories\Eloquent\TagRepository;
use Coyote\Services\Elasticsearch\Crawler;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Tag as Stream_Tag;
use Coyote\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TagsController extends BaseController
{
    public function __construct(private TagRepository $tag)
    {
        parent::__construct();
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
            ->addSelect(DB::raw('resources->>\'Coyote\Microblog\' as microblogs'))
            ->addSelect(DB::raw('resources->>\'Coyote\Topic\' as topics'))
            ->addSelect(DB::raw('resources->>\'Coyote\Job\' as jobs'))
            ->leftJoin('tag_categories', 'tag_categories.id', '=', 'category_id');

        $grid = $this->gridBuilder()
            ->createGrid(TagsGrid::class)
            ->setSource(new EloquentSource($builder));

        return $this->view('adm.tags.home')->with('grid', $grid);
    }

    public function edit(Tag $tag): View
    {
        $this->breadcrumb->push('Edycja', route('adm.tags.save', ['tag' => $tag]));
        $form = $this->createForm(TagForm::class, $tag);

        return $this->view('adm.tags.save')->with('form', $form);
    }

    public function save(Tag $tag): RedirectResponse
    {
        $form = $this->createForm(TagForm::class, $tag);
        $form->validate();

        $tag->fill($form->all());

        $this->transaction(function () use ($form, $tag) {
            if ($form->getRequest()->hasFile('logo')) {
                $tag->logo->upload($form->getRequest()->file('logo'));
            }

            $tag->save();

            stream(Stream_Update::class, (new Stream_Tag())->map($tag));
        });

        (new Crawler())->index($tag);

        return redirect()->route('adm.tags')->with('success', 'Zmiany zostały zapisane.');
    }

    public function delete(Tag $tag): RedirectResponse
    {
        $tag->delete();

        (new Crawler())->delete($tag);

        stream(Stream_Delete::class, (new Stream_Tag())->map($tag));

        return redirect()->route('adm.tags')->with('success', 'Tag został usunięty.');
    }
}
