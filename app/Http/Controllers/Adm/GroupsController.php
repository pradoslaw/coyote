<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Forms\Group\GroupForm;
use Coyote\Http\Grids\Adm\GroupsGrid;
use Coyote\Repositories\Contracts\GroupRepositoryInterface as GroupRepository;
use Coyote\Services\Grid\Source\EloquentDataSource;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Group as Stream_Group;
use Illuminate\Contracts\Cache\Repository as Cache;

class GroupsController extends BaseController
{
    /**
     * @var GroupRepository
     */
    protected $group;

    /**
     * @param GroupRepository $group
     */
    public function __construct(GroupRepository $group)
    {
        parent::__construct();

        $this->group = $group;
        $this->breadcrumb->push('Grupy', route('adm.groups'));
    }

    /**
     * @inheritdoc
     */
    public function index()
    {
        $grid = $this->getGrid()->createGrid(GroupsGrid::class);
        $grid->setSource(new EloquentDataSource($this->group));

        return $this->view('adm.groups.home')->with('grid', $grid);
    }

    /**
     * @param \Coyote\Group $group
     * @return \Illuminate\View\View
     */
    public function edit($group)
    {
        $this->breadcrumb->push($group->name);
        $form = $this->getForm($group);

        return $this->view('adm.groups.save')->with('form', $form);
    }

    /**
     * @param \Coyote\Group $group
     * @return \Illuminate\View\View
     */
    public function save($group)
    {
        $form = $this->getForm($group);
        $form->validate();

        $group->fill($form->all());

        $this->transaction(function () use ($group, $form) {
            $group->save();

            foreach ($group->permissions()->get() as $permission) {
                $group->permissions()->updateExistingPivot(
                    $permission->id,
                    ['value' => in_array($permission->id, $form->permissions->getValue())]
                );
            }

            app(Cache::class)->forget('permissions');
            stream(Stream_Update::class, (new Stream_Group())->map($group));
        });

        return redirect()->route('adm.groups')->with('success', 'Zapisano ustawienia grupy.');
    }

    /**
     * @param \Coyote\Group $group
     * @return \Coyote\Services\FormBuilder\Form
     */
    private function getForm($group)
    {
        return $this->createForm(GroupForm::class, $group);
    }
}
