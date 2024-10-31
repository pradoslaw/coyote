<?php
namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Decorators\LongText;
use Boduch\Grid\Order;
use Boduch\Grid\Row;
use Coyote\Flag;
use Coyote\Services\Grid\Grid;

class FlagsGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('created_at', 'DESC'))
            ->addColumn('created_at', ['title' => 'Data zgłoszenia'])
            ->addColumn('flag_type', [
                'placeholder' => '--',
                'title'       => 'Typ',
            ])
            ->addColumn('text', [
                'title'      => 'Treść raportu',
                'decorators' => [new LongText()],
                'clickable'  => fn(Flag $flag) => link_to($flag->url, $flag->text),
            ])
            ->addColumn('user_name', [
                'title'       => 'Zgłaszający',
                'sortable'    => true,
                'placeholder' => '--',
                'clickable'   => fn(Flag $flag) => link_to_route('adm.users.save', $flag->user_name, [$flag->user_id]),
            ])
            ->addColumn('moderator_name', [
                'title'       => 'Zamknięty przez',
                'clickable'   => fn(Flag $flag) => link_to_route(
                    'adm.users.save',
                    $flag->moderator_name,
                    [$flag->moderator_id]),
                'placeholder' => '--',
            ])
            ->after(function (Row $row) {
                if (!empty($row->raw('deleted_at'))) {
                    $row->class = 'strikeout';
                }
            });
    }
}
