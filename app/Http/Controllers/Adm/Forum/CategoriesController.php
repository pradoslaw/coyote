<?php

namespace Coyote\Http\Controllers\Adm\Forum;

use Boduch\Grid\Source\CollectionSource;
use Coyote\Events\ForumSaved;
use Coyote\Http\Controllers\Adm\BaseController;
use Coyote\Http\Forms\Forum\ForumForm;
use Coyote\Http\Grids\Adm\Forum\CategoriesGrid;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Services\Forum\TreeBuilder\Builder;
use Coyote\Services\Forum\TreeBuilder\FlatDecorator;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;

class CategoriesController extends BaseController
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @param ForumRepository $forum
     */
    public function __construct(ForumRepository $forum)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->breadcrumb->push('Forum', route('adm.forum.categories'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $treeBuilder = new FlatDecorator(new Builder($this->forum->orderBy('order')->get()));

        $grid = $this
            ->gridBuilder()
            ->createGrid(CategoriesGrid::class)
            ->setSource(new CollectionSource(collect($treeBuilder->build())))
            ->setEnablePagination(false);

        return $this->view('adm.forum.categories.home')->with('grid', $grid);
    }

    /**
     * @param int|null $id
     * @return \Illuminate\View\View
     */
    public function edit($id = null)
    {
        $forum = $this->forum->findOrNew($id);
        $this->breadcrumb->push($forum->name ?? 'Dodaj nową');

        return $this->view('adm.forum.categories.save')->with('form', $this->getForm($forum));
    }

    /**
     * @param int|null $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($id = null)
    {
        /** @var \Coyote\Forum $forum */
        $forum = $this->forum->findOrNew($id);

        $form = $this->getForm($forum);
        $form->validate();

        $original = $forum->getRawOriginal();
        $forum->fill($form->all());

        $this->transaction(function () use ($form, $forum) {
            $groups = (array) $form->get('access')->getValue();

            $forum->is_prohibited = count($groups);
            $forum->save();
            $forum->access()->delete();

            foreach ($groups as $groupId) {
                $forum->access()->create(['group_id' => $groupId]);
            }

            $this->flushCache();
        });

        event(new ForumSaved($forum, $original));

        return redirect()->route('adm.forum.categories')->with('success', 'Zmiany zostały zapisane.');
    }

    /**
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function move($id, Request $request)
    {
        $this->validate($request, ['mode' => 'required|in:up,down']);

        $mode = $request->get('mode');
        $this->forum->$mode($id);

        $this->flushCache();

        return redirect()->route('adm.forum.categories')->with('success', 'Pozycja została zmieniona.');
    }

    private function flushCache()
    {
        app(Cache::class)->tags('forum-order')->flush();
    }

    /**
     * @param \Coyote\Forum $forum
     * @return \Coyote\Services\FormBuilder\Form
     */
    private function getForm($forum)
    {
        return $this->createForm(ForumForm::class, $forum);
    }
}
