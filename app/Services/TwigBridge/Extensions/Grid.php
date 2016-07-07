<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Services\Grid\CellInterface;
use Coyote\Services\Grid\Column;
use Coyote\Services\Grid\Row;
use Coyote\Services\Grid\Grid as GridObject;
use Twig_Extension;
use Twig_SimpleFunction;

class Grid extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_Grid';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('grid', [&$this, 'grid'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('grid_column', [&$this, 'column'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('grid_row', [&$this, 'row'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('grid_cell', [&$this, 'cell'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('grid_filter', [&$this, 'filter'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('grid_no_data', [&$this, 'noData'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param GridObject $grid
     * @return string
     */
    public function grid(GridObject $grid)
    {
        return $grid->render();
    }

    /**
     * @param Column $column
     * @return string
     */
    public function column(Column $column)
    {
        if ($column->isSortable()) {
            $order = $column->getGrid()->getOrder();

            $parameters = array_merge(
                $column->getGrid()->getRequest()->all(),
                [
                    'column' => $column->getName(),
                    'direction' => $order->getDirection() == 'desc' ? 'asc' : 'desc'
                ]
            );

            $text = link_to(
                $column->getGrid()->getRequest()->path() . '?' . http_build_query($parameters),
                $column->getTitle(),
                ['class' => 'sort ' . ($order->getColumn() == $column->getName() ? strtolower($order->getDirection()) : '')]
            );
        } else {
            $text = $column->getTitle();
        }

        return $column->getGrid()->getHtmlBuilder()->tag('th', (string) $text);
    }

    /**
     * @param Column $column
     * @return string
     */
    public function filter(Column $column)
    {
        $filter = '';

        if ($column->isFilterable()) {
            $filter = (string) $column->getFilter()->render();
        }

        return $column->getGrid()->getHtmlBuilder()->tag('th', (string) $filter);
    }

    /**
     * @param Row $row
     * @return string
     */
    public function row(Row $row)
    {
        $cells = '';
        foreach ($row as $cell) {
            $cells .= $this->cell($cell);
        }

        return $row->getGrid()->getHtmlBuilder()->tag('tr', $cells, $row->getAttributes());
    }

    /**
     * @param CellInterface $cell
     * @return string
     */
    public function cell(CellInterface $cell)
    {
        return $cell->getColumn()->getGrid()->getHtmlBuilder()->tag('td', (string) $cell->getValue());
    }

    /**
     * @param GridObject $grid
     * @return \Illuminate\Support\HtmlString
     */
    public function noData(GridObject $grid)
    {
        return $grid->getHtmlBuilder()->tag(
            'td',
            (string) $grid->getNoDataMessage(),
            ['colspan' => count($grid->getColumns()), 'style' => 'text-align: center']
        );
    }
}
