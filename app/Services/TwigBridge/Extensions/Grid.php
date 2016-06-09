<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Services\Grid\Cell;
use Coyote\Services\Grid\Columns\Column;
use Coyote\Services\Grid\Grid as Grid_Object;
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
            new Twig_SimpleFunction('grid_cell', [&$this, 'cell'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param Grid_Object $grid
     * @return string
     */
    public function grid(Grid_Object $grid)
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
            $direction = $column->getGrid()->getOrder()->getDirection();

            $parameters = array_merge(
                $column->getGrid()->getRequest()->all(),
                [
                    'column' => $column->getName(),
                    'direction' => $direction == 'desc' ? 'asc' : 'desc'
                ]
            );

            $text = link_to(
                $column->getGrid()->getRequest()->path() . '?' . http_build_query($parameters),
                $column->getTitle(),
                ['class' => "sort " . ($direction == $column->getName() ? strtolower($direction) : '')]
            );
        } else {
            $text = $column->getTitle();
        }

        return $column->getGrid()->getHtmlBuilder()->tag('th', (string) $text);
    }

    /**
     * @param Cell $cell
     * @return string
     */
    public function cell(Cell $cell)
    {
        return $cell->getColumn()->getGrid()->getHtmlBuilder()->tag('td', (string) $cell->getValue());
    }
}
