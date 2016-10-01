<?php

namespace Coyote\Http\Grids\Adm\Forum;

use Coyote\Forum;
use Coyote\Services\Grid\Components\CreateButton;
use Coyote\Services\Grid\Grid;
use Boduch\Grid\Components\EditButton;

class ReasonsGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->addColumn('id', [
                'title' => 'ID'
            ])
            ->addColumn('name', [
                'title' => 'Nazwa',
                'clickable' => function (Forum\Reason $reason) {
                    return link_to_route('adm.forum.reasons.save', $reason->name, [$reason->id]);
                }
            ])
            ->addRowAction(new EditButton(function (Forum\Reason $reason) {
                return route('adm.forum.reasons.save', [$reason->id]);
            }))
            ->addComponent(
                new CreateButton(
                    route('adm.forum.reasons.save'),
                    'Nowa pozycja'
                )
            );
    }
}
