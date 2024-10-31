<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Components\EditButton;
use Boduch\Grid\Decorators\FormatDateRelative;
use Boduch\Grid\Order;
use Coyote\Group;
use Coyote\Services\Grid\Components\CreateButton;
use Coyote\Services\Grid\Grid;

class GroupsGrid extends Grid
{
    public function buildGrid(): void
    {
        $this
            ->setDefaultOrder(new Order('id', 'asc'))
            ->addColumn('id', [
                'title'    => 'ID',
                'sortable' => true,
            ])
            ->addColumn('name', [
                'title'     => 'Nazwa',
                'clickable' => function (Group $group) {
                    return link_to_route('adm.groups.save', $group->name, [$group->id]);
                },
            ])
            ->addColumn('description', [
                'title' => 'Opis',
            ])
            ->addColumn('created_at', [
                'title'      => 'Data dodania',
                'decorators' => [new FormatDateRelative('nigdy')],
            ])
            ->addColumn('updated_at', [
                'title'      => 'Data aktualizacji',
                'decorators' => [new FormatDateRelative('nigdy')],
            ])
            ->addRowAction(new EditButton(function (Group $group) {
                return route('adm.groups.save', [$group->id]);
            }))
            ->addComponent(
                new CreateButton(
                    route('adm.groups.save'),
                    'Nowa grupa',
                ),
            );
    }
}
