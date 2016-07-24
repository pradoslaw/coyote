<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Decorators\DateTimeFormat;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Order;
use Coyote\Services\Grid\RowActions\EditButton;

class GroupsGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('id', 'asc'))
            ->addColumn('id', [
                'title' => 'ID',
                'sortable' => true
            ])
            ->addColumn('name', [
                'title' => 'Nazwa',
                'clickable' => function ($group) {
                    /** @var \Coyote\Group $group */
                    return link_to_route('adm.groups.save', $group->name, [$group->id]);
                }
            ])
            ->addColumn('description', [
                'title' => 'Opis'
            ])
            ->addColumn('created_at', [
                'title' => 'Data dodania',
                'decorators' => [new DateTimeFormat('Y-m-d')]
            ])
            ->addColumn('updated_at', [
                'title' => 'Data aktualizacji',
                'decorators' => [new DateTimeFormat('Y-m-d')]
            ])
            ->addRowAction(new EditButton(function ($group) {
                return route('adm.groups.save', [$group->id]);
            }))
            ->setData([
                'add_url' => route('adm.groups.save'),
                'add_label' => 'Nowa grupa'
            ]);
    }
}
