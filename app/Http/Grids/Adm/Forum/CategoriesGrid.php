<?php

namespace Coyote\Http\Grids\Adm\Forum;

use Coyote\Services\Grid\Decorators\StrLimit;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\RowActions\EditButton;

class CategoriesGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->addColumn('id', [
                'title' => 'ID'
            ])
            ->addColumn('name', [
                'title' => 'Nazwa',
                'clickable' => function ($forum) {
                    return link_to_route('adm.forum.categories.save', $forum->name, [$forum->id]);
                }
            ])
            ->addColumn('description', [
                'title' => 'Opis',
                'decorators' => [new StrLimit()]
            ])
            ->addRowAction(new EditButton(function ($forum) {
                /** @var \Coyote\Forum $forum */
                return route('adm.forum.categories.save', [$forum->id]);
            }));
    }
}
