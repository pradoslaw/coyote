<?php

namespace Coyote\Http\Grids\Wiki;

use Boduch\Grid\Cell;
use Boduch\Grid\Order;
use Boduch\Grid\Row;
use Coyote\Services\Grid\Components\SubmitButton;
use Coyote\Services\Grid\Decorators\TextSize;
use Coyote\Services\Grid\Grid;

class LogGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('wiki_log.created_at', 'desc'))
            ->addColumn('user_id', [
                'title' => 'Użytkownik',
                'clickable' => function ($row) {
                    return link_to_route('profile', $row->user_name, [$row->user_id], ['data-user-id' => $row->user_id]);
                }
            ])
            ->addColumn('comment', [
                'title' => 'Komentarz',
                'render' => function ($row) {
                    /** @var Cell $this */
                    $html = $this->getColumn()->getGrid()->getGridHelper()->getHtmlBuilder();

                    $this->setValue(
                        $html->tag('strong', (string) $html->link($row->path, $row->title)) .
                        $html->tag('p', (string) htmlspecialchars($this->getValue()), ['class' => 'text-muted'])
                    );

                    return $this->getValue();
                }
            ])
            ->addColumn('length', [
                'title' => 'Rozmiar',
                'decorators' => [new TextSize()]
            ])
            ->addColumn('diff', [
                'title' => 'Różnica',
                'decorators' => [new TextSize()]
            ])
            ->addColumn('created_at', [
                'title' => 'Data modyfikacji',
                'decorators' => [$this->getDateTimeDecorator()]
            ]);
    }

    public function addComparisionButtons()
    {
        $form = $this->getGridHelper()->getFormBuilder();
        $original = $this->columns;

        $this->columns = [];

        $this
            ->addColumn('r1', [
                'render' => function ($row) use ($form) {
                    return $form->radio('r1', $row->id);
                }
            ])
            ->addColumn('r2', [
                'render' => function ($row) use ($form) {
                    return $form->radio('r2', $row->id);
                }
            ])
            ->after(function (Row $row) {
                static $index = 0;

                $index++;
                if ($index === 1) {
                    $row->get('r2')->setValue('');
                }

                if ($index === count($this->rows)) {
                    $row->get('r1')->setValue('');
                }
            })
            ->addComponent(new SubmitButton('', 'Porównaj wersje'));

        foreach ($original as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }
}
