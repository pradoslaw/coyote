<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Decorators\InputText;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Order;
use Coyote\Services\Grid\RowActions\DeleteButton;

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
            ->addRowAction(new DeleteButton(function () {
                return '#confirm';
            }));
    }
}
