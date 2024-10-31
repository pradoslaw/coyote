<?php

namespace Coyote\Http\Grids\Adm\Forum;

use Boduch\Grid\Decorators\Boolean;
use Boduch\Grid\Decorators\LongText;
use Boduch\Grid\Row;
use Coyote\Forum;
use Coyote\Services\Grid\Components\CreateButton;
use Coyote\Services\Grid\Grid;
use Boduch\Grid\Components\EditButton;

class CategoriesGrid extends Grid
{
    const UP = 'up';
    const DOWN = 'down';

    /**
     * @var array
     */
    protected $defaultOrder = [];

    public function buildGrid()
    {
        $html = $this->getGridHelper()->getHtmlBuilder();

        $up = $html->tag('i', '', ['class' => 'fa fa-arrow-up']);
        $down = $html->tag('i', '', ['class' => 'fa fa-arrow-down']);

        $this
            ->addColumn('id', [
                'title' => 'ID'
            ])
            ->addColumn('name', [
                'title' => 'Nazwa',
                'render' => function (Forum $forum) {
                    if ($forum->parent_id) {
                        $forum->name = '—— ' . $forum->name;
                    }

                    return link_to_route('adm.forum.categories.save', $forum->name, [$forum->id]);
                }
            ])
            ->addColumn('description', [
                'title' => 'Opis',
                'decorators' => [new LongText()]
            ])
            ->addColumn('order', [
                'title' => 'Położenie'
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
            ->addRowAction(new EditButton(function (Forum $forum) {
                return route('adm.forum.categories.save', [$forum->id]);
            }))
            ->addComponent(
                new CreateButton(
                    route('adm.forum.categories.save'),
                    'Nowa kategoria'
                )
            )
            ->after(function (Row $row) use ($up, $down) {
                $row->get('order')->getColumn()->setAutoescape(false);

                $row->get('order')->setValue(
                    $this->linkToRoute(self::UP, $row->raw('id'), $up) .
                    $this->linkToRoute(self::DOWN, $row->raw('id'), $down)
                );
            });
    }

    /**
     * @param string $mode
     * @param int $forumId
     * @param string $title
     * @return string
     */
    protected function linkToRoute($mode, $forumId, $title)
    {
        return '<a href="' . route('adm.forum.categories.move', [$forumId]) . '?mode=' . $mode . '">' . $title . '</a>';
    }
}
