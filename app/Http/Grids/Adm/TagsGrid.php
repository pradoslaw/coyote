<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Components\CreateButton;
use Coyote\Services\Grid\Grid;
use Boduch\Grid\Order;
use Coyote\Tag;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Select;
use Boduch\Grid\Filters\Text;

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
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'tags.name'])
            ])
            ->addColumn('created_at', [
                'placeholder' => '--',
                'title' => 'Data dodania',
                'decorators' => [$this->getDateTimeDecorator()]
            ])
            ->addColumn('category', [
                'title' => 'Kategoria',
                'sortable' => true,
                'placeholder' => '--',
                'filter' => new Select(['options' => Tag\Category::pluck('name', 'id')->toArray(), 'name' => 'category_id'])
            ])
            ->addColumn('topics', [
                'title' => 'Wątki',
                'sortable' => true
            ])
            ->addColumn('jobs', [
                'title' => 'Oferty Pracy',
                'sortable' => true
            ])
            ->addColumn('microblogs', [
                'title' => 'Mikroblogi',
                'sortable' => true
            ])
            ->addComponent(
                new CreateButton(
                    route('adm.tags.save'),
                    'Nowy tag'
                )
            );
    }
}
