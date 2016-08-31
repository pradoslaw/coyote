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
        $this->assertEquals('First name', $grid->getColumns()['name']->getTitle());
    }

    public function testAddColumnWithDecorators()
    {
        $grid = new Grid($this->gridHelper);
        $grid->addColumn('name', [
            'title' => 'First name',
            'clickable' => function () {
                return '';
            },
            'decorators' => [
                new \Boduch\Grid\Decorators\Url()
            ]
        ]);

        $column = $grid->getColumns()['name'];

        $this->assertEquals(2, count($column->getDecorators()));
    }
}