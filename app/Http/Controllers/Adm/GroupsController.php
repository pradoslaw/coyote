<?php

namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Group;
use Coyote\Http\Forms\Group\GroupForm;
use Coyote\Http\Grids\Adm\GroupsGrid;
use Coyote\Repositories\Contracts\GroupRepositoryInterface as GroupRepository;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Group as Stream_Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
        $grid = $this->gridBuilder()->createGrid(GroupsGrid::class);
        $grid->setSource(new EloquentSource($this->group));

        return $this->view('adm.groups.home')->with('grid', $grid);
    }

    public function edit(Group $group): View
    {
        $this->breadcrumb->push($group->name ?? 'Dodaj nową', route('adm.groups.save', ['group' => $group]));
        $form = $this->getForm($group);

        return $this->view('adm.groups.save')->with('form', $form);
    }

    public function save(Group $group): RedirectResponse
    {
        $form = $this->getForm($group);
        $form->validate();

        $group->fill($form->all());

        $this->transaction(function () use ($group, $form) {
            $group->save();

            foreach ($group->permissions()->get() as $permission) {
                $group->permissions()->updateExistingPivot(
                    $permission->id,
                    ['value' => in_array($permission->id, $form->permissions->getValue())],
                );
            }

            $group->users()->sync((array)$form->users->getValue()); // array can be empty
            // update group name in users table
            $group->users()->where('users.group_id', $group->id)->update(['group_name' => $group->name]);

            $this->flushPermission();

            stream(Stream_Update::class, (new Stream_Group())->map($group));
        });

        return redirect()->route('adm.groups')->with('success', 'Zapisano ustawienia grupy.');
    }

    /**
     * @param Group $group
     * @return RedirectResponse
     */
    public function delete($group)
    {
        if ($group->system) {
            abort(401);
        }

        $this->transaction(function () use ($group) {
            $group->delete();

            $this->flushPermission();
            stream(Stream_Delete::class, (new Stream_Group())->map($group));
        });

        return redirect()->route('adm.groups')->with('success', 'Grupa została usunięta.');
    }

    /**
     * @param Group $group
     * @return \Coyote\Services\FormBuilder\Form
     */
    private function getForm($group)
    {
        return $this->createForm(GroupForm::class, $group);
    }
}
