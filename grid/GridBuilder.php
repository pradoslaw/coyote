<?php

namespace Boduch\Grid;

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

        if (method_exists($grid, 'boot')) {
            // call method boot() if exists
            $this->container->call([&$grid, 'boot']);
        }

        $grid->buildGrid();

        return $grid;
    }
}
