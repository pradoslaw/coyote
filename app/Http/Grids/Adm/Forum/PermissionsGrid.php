<?php

namespace Coyote\Http\Grids\Adm\Forum;

use Coyote\Repositories\Contracts\GroupRepositoryInterface as GroupRepository;
use Coyote\Services\Grid\Decorators\Boolean;
use Coyote\Services\Grid\Decorators\StrLimit;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\RowActions\EditButton;

class PermissionsGrid extends Grid
{
    public function buildGrid()
    {
        $group = app(GroupRepository::class);
        $form = $this->getFormBuilder();

        $this
            ->addColumn('name', [
                'title' => 'Nazwa',
            ])
            ->addColumn('description', [
                'title' => 'Opis'
            ]);

        foreach ($group->all() as $row) {
            $this->addColumn('group_' . $row->id, [
                'title' => $row->name,
//                'render' => function ($group) use ($form) {
//                    return $form->checkbox('')
//                }
            ]);
        }
    }
}
