<?php

use Boduch\Grid;

if (!function_exists('grid')) {
    /**
     * @param Grid\Grid $grid
     * @return string
     */
    function grid(Grid\Grid $grid)
    {
        return $grid->render();
    }
}

if (!function_exists('grid_column')) {
    /**
     * @param Grid\Column $column
     * @return string
     */
    function grid_column(Grid\Column $column)
    {
        if ($column->isSortable()) {
            $order = $column->getGrid()->getOrder();

            $parameters = array_merge($column->getGrid()->getGridHelper()->getRequest()->all(), [
                'column' => $column->getName(),
                'direction' => $order->getDirection() == 'desc' ? 'asc' : 'desc'
            ]);

            $text = link_to(
                $column->getGrid()->getGridHelper()->getRequest()->path() . '?' . http_build_query($parameters),
                $column->getTitle(),
                ['class' => 'sort ' . ($order->getColumn() == $column->getName() ? strtolower($order->getDirection()) : '')]
            );
        } else {
            $text = $column->getTitle();
        }

        return $column->getGrid()->getGridHelper()->tag('th', (string) $text);
    }
}

if (!function_exists('grid_filter')) {
    /**
     * @param Grid\Column $column
     * @return string
     */
    function grid_filter(Grid\Column $column)
    {
        $filter = '';

        if ($column->isFilterable()) {
            $filter = (string) $column->getFilter()->render();
        }

        return $column->getGrid()->getGridHelper()->tag('th', (string) $filter);
    }
}

if (!function_exists('grid_row')) {
    /**
     * @param Grid\Row $row
     * @return string
     */
    function grid_row(Grid\Row $row)
    {
        $cells = '';
        foreach ($row as $cell) {
            $cells .= grid_cell($cell);
        }

        return $row->getGrid()->getGridHelper()->tag('tr', $cells, (array) $row->attributes()->all());
    }
}

if (!function_exists('grid_cell')) {
    /**
     * @param Grid\CellInterface $cell
     * @return string
     */
    function grid_cell(Grid\CellInterface $cell)
    {
        return $cell->getColumn()->getGrid()->getGridHelper()->tag(
            'td',
            (string) $cell->getValue(),
            (array) $cell->attributes()->all()
        );
    }
}

if (!function_exists('grid_empty')) {
    /**
     * @param Grid\Grid $grid
     * @return \Illuminate\Support\HtmlString
     */
    function grid_empty(Grid\Grid $grid)
    {
        return $grid->getGridHelper()->tag(
            'td',
            (string) $grid->getEmptyMessage(),
            ['colspan' => count($grid->getColumns()), 'style' => 'text-align: center']
        );
    }
}
