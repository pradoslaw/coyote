<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Decorators\DateTimeFormat;
use Coyote\Group;
use Coyote\Services\Grid\Components\CreateButton;
use Coyote\Services\Grid\Grid;
use Boduch\Grid\Order;
use Boduch\Grid\Components\EditButton;

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
                'clickable' => function (Group $group) {
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
            ->addRowAction(new EditButton(function (Group $group) {
                return route('adm.groups.save', [$group->id]);
            }))
            ->addComponent(
                new CreateButton(
                    route('adm.groups.save'),
                    'Nowa grupa'
                )
            );
    }
}
