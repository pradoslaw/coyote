<?php

namespace Coyote\Http\Grids\User;

use Coyote\Services\Grid\Grid;
use Boduch\Grid\Order;

class StatsGrid extends Grid
{
    public function buildGrid()
    {
        $self = $this;

        $this
            ->setDefaultOrder(new Order('posts_count', 'desc'))
            ->addColumn('subject', [
                'title' => 'Kategoria forum',
                'clickable' => function ($row) {
                    return link_to_route(
                        'forum.category',
                        $row->name,
                        [$row->slug]
                    );
                },
            ])
            ->addColumn('posts_count', [
                'title' => 'Ilość postów'
            ])
            ->addColumn('votes_count', [
                'title' => 'Oceny'
            ])
            ->addColumn('sum', [
                'title' => 'Sumuj',
                'render' => function ($row) use ($self) {
                    return $self->checkbox($row->id);
                }
            ]);
    }

    /**
     * @param string $value
     * @return \Illuminate\Support\HtmlString
     */
    public function checkbox($value)
    {
        $html = $this->getGridHelper()->getHtmlBuilder();
        $form = $this->getGridHelper()->getFormBuilder();

        $id = 'id-' . uniqid();

        $label = $html->tag('label', '', ['for' => $id]);
        $checkbox = $form->checkbox('count[]', $value, true, ['class' => 'switcher', 'id' => $id]);

        return $html->tag('div', $checkbox . $label, ['class' => 'checkbox', 'style' => 'margin: 0']);
    }
}
