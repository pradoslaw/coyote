<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Services\Grid\Columns\Column;
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
            new Twig_SimpleFunction('grid_column', [&$this, 'column'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('grid_render', [&$this, 'render'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param Column $column
     * @return string
     */
    public function column(Column $column)
    {
        if ($column->isSortable()) {
            $direction = $column->getGrid()->getOrder()['direction'];

            $parameters = array_merge(
                $column->getGrid()->getRequest()->all(),
                [
                    'column' => $column->getName(),
                    'direction' => $direction == 'desc' ? 'asc' : 'desc'
                ]
            );

            return link_to(
                $column->getGrid()->getRequest()->path() . '?' . http_build_query($parameters),
                $column->getTitle(),
                ['class' => "sort " . ($direction == $column->getName() ? strtolower($direction) : '')]
            );
        } else {
            return $column->getTitle();
        }
    }

    /**
     * @param Column $column
     * @param string $data
     * @return string
     */
    public function render(Column $column, $data)
    {
        return $column->render($data);
    }
}
