<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Components\CreateButton;
use Coyote\Services\Grid\Decorators\InputText;
use Coyote\Services\Grid\Grid;
use Boduch\Grid\Order;
use Boduch\Grid\Components\DeleteButton;

class WordsGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('id', [
                'title' => 'ID',
                'sortable' => true
            ])
            ->addColumn('word', [
                'title' => 'Fraza do odszukania',
                'decorators' => [new InputText()]
            ])
            ->addColumn('replacement', [
                'title' => 'Fraza do zastąpienia',
                'decorators' => [new InputText()]
            ])
            ->addComponent(
                new CreateButton(
                    '',
                    'Dodaj nowy',
                    ['title' => 'Dodaj wyraz do bazy danych']
                )
            )
            ->addRowAction(new DeleteButton(function () {
                return '#confirm';
            }));
    }
}
