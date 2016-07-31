<?php

namespace Coyote\Http\Grids\Adm\Forum;

use Coyote\Services\Grid\Decorators\Boolean;
use Coyote\Services\Grid\Decorators\StrLimit;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\RowActions\EditButton;

class CategoriesGrid extends Grid
{
    /**
     * @var array
     */
    protected $defaultOrder = [];

    public function buildGrid()
    {
        $this
            ->addColumn('id', [
                'title' => 'ID'
            ])
            ->addColumn('name', [
                'title' => 'Nazwa',
                'render' => function ($forum) {
                    /** @var \Coyote\Forum $forum */
                    if ($forum->parent_id) {
                        $forum->name = '—— ' . $forum->name;
                    }

                    return link_to_route('adm.forum.categories.save', $forum->name, [$forum->id]);
                }
            ])
            ->addColumn('description', [
                'title' => 'Opis',
                'decorators' => [new StrLimit()]
            ])
            ->addColumn('require_tag', [
                'title' => 'Wymagaj tagu',
                'decorators' => [new Boolean()]
            ])
            ->addColumn('enable_reputation', [
                'title' => 'Zliczaj reputację',
                'decorators' => [new Boolean()]
            ])
            ->addColumn('enable_anonymous', [
                'title' => 'Zapis dla anonimów',
                'decorators' => [new Boolean()]
            ])
            ->addRowAction(new EditButton(function ($forum) {
                /** @var \Coyote\Forum $forum */
                return route('adm.forum.categories.save', [$forum->id]);
            }))
            ->setData([
                'add_url' => route('adm.forum.categories.save'),
                'add_label' => 'Nowa kategoria'
            ]);
    }
}
