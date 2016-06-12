<?php

namespace Coyote\Services\Grid;

use Illuminate\Contracts\Container\Container;

class GridBuilder
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container  $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $gridClass
     * @return Grid
     */
    public function createGrid($gridClass)
    {
        if (!class_exists($gridClass)) {
            throw new \InvalidArgumentException(
                'Grid class with name ' . $gridClass . ' does not exist.'
            );
        }

        /** @var Grid $grid */
        $grid = $this->container->make($gridClass);
        $grid->buildGrid();

        return $grid;
    }

    /**
     * @return Grid
     */
    public function createBuilder()
    {
        return $this->createGrid(Grid::class);
    }
}
