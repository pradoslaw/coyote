<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Grid;
use Boduch\Grid\Order;
use Coyote\Tag;

class TagsGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('created_at', 'desc'))
            ->addColumn('name', [
                'title' => 'Nazwa',
                'sortable' => true,
                'clickable' => function (Tag $tag) {
                    return link_to_route('adm.tags.save', $tag->name, [$tag->id]);
                }
            ])
            ->addColumn('created_at', [
                'placeholder' => '--',
                'title' => 'Data dodania',
                'decorators' => [$this->getDateTimeDecorator()]
            ])
            ->addColumn('category', [
                'title' => 'Kategoria',
                'sortable' => true,
                'placeholder' => '--'
            ]);
    }
}
