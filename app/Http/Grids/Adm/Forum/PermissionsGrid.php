<?php

namespace Coyote\Http\Grids\Adm\Forum;

use Coyote\Repositories\Contracts\GroupRepositoryInterface as GroupRepository;
use Coyote\Services\Grid\Grid;

class PermissionsGrid extends Grid
{
    /**
     * @var array
     */
    protected $defaultOrder = [];

    /**
     * @var GroupRepository
     */
    protected $group;

    /**
     * @param GroupRepository $group
     */
    public function boot(GroupRepository $group)
    {
        $this->group = $group;
    }

    public function buildGrid()
    {
        $form = $this->getFormBuilder();

        $this
            ->addColumn('name', [
                'title' => 'Nazwa',
            ])
            ->addColumn('description', [
                'title' => 'Opis'
            ]);

        foreach ($this->group->all() as $group) {
            $columnName = 'group_' . $group->id;

            // column for each group
            $this->addColumn($columnName, [
                'title' => $group->name,
                'render' => function ($row) use ($columnName, $form, $group) {
                    $isChecked = (bool) $row[$columnName];
                    // transfer 1 or 0 to checkbox
                    return $form->checkbox('group[' . $group->id . '][' . $row['permission_id'] . ']', 1, $isChecked);
                }
            ]);
        }
    }
}
