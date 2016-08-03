<?php

use Boduch\Grid\Grid;

class GridTest extends GridBuilderTestCase
{
    public function testAddColumn()
    {
        $grid = new Grid($this->gridHelper);
        $grid->addColumn('name', [
            'title' => 'First name'
        ]);

        $this->assertInstanceOf(\Boduch\Grid\Column::class, $grid->getColumns()['name']);
    }
}