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
        $grid->addColumn('sex', [
            'title' => 'Sex',
            'sortable' => true
        ]);

        $this->assertInstanceOf(\Boduch\Grid\Column::class, $grid->getColumns()['name']);
        $this->assertEquals('First name', $grid->getColumns()['name']->getTitle());

        $this->assertInstanceOf(\Boduch\Grid\Column::class, $grid->getColumns()['sex']);
        $this->assertEquals('Sex', $grid->getColumns()['sex']->getTitle());
        $this->assertTrue($grid->getColumns()['sex']->isSortable());
    }

    public function testAddColumnWithoutTitle()
    {
        $grid = new Grid($this->gridHelper);
        $grid->addColumn('first_name');
        $grid->addColumn('sex');

        $this->assertEquals('First Name', $grid->getColumns()['first_name']->getTitle());
        $this->assertEquals('Sex', $grid->getColumns()['sex']->getTitle());
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
        $this->assertInstanceOf(\Boduch\Grid\Decorators\DecoratorInterface::class, $column->getDecorators()[0]);
        $this->assertInstanceOf(\Boduch\Grid\Decorators\DecoratorInterface::class, $column->getDecorators()[1]);
    }

    public function testRenderColumnWithDecorator()
    {
        $grid = $this->getSampleGrid();
        $rows = $grid->getRows();

        $this->assertInstanceOf(\Boduch\Grid\Rows::class, $rows);
        $this->assertInstanceOf(\Boduch\Grid\Row::class, $rows[0]);

        $this->assertEquals("<a href=\"http://4programmers.net\">http://4programmers.net</a>", (string) $rows[0]->getValue('website'));
        $this->assertEquals("<a href=\"http://4programmers.net\">1</a>", (string) $rows[0]->getValue('id'));
    }

    public function testBuildGridWithEachCallbackAndModifyColumnValue()
    {
        $grid = $this->getSampleGrid();
        $grid->after(function (Boduch\Grid\Row $row) {
            $row->get('website')->setValue('');
        });

        $rows = $grid->getRows();

        $this->assertEquals('', (string) $rows[0]->getValue('website'));
    }

    public function testBuildGridAndAddRowClass()
    {
        $grid = $this->getSampleGrid();
        $grid->after(function (Boduch\Grid\Row $row) {
            $row->class = 'foo';
        });

        $rows = $grid->getRows();

        $this->assertEquals('foo', (string) $rows[0]->class);
    }

    public function testAutoescapeCellOutput()
    {
        $grid = new Grid($this->gridHelper);
        $grid->addColumn('xss', [
            'title' => 'xss'
        ]);

        $collection = collect([
            ['xss' => '<xss>']
        ]);

        $source = new \Boduch\Grid\Source\CollectionSource($collection);
        $grid->setSource($source);

        $rows = $grid->getRows();

        $this->assertEquals('&lt;xss&gt;', (string) $rows[0]->getValue('xss'));
    }

    public function testAutoescapeCellOutputWithStrLimitDecorator()
    {
        $grid = new Grid($this->gridHelper);
        $grid->addColumn('xss', [
            'title' => 'xss',
            'decorators' => [new \Boduch\Grid\Decorators\StrLimit()]
        ]);

        $collection = collect([
            ['xss' => '<xss>']
        ]);

        $source = new \Boduch\Grid\Source\CollectionSource($collection);
        $grid->setSource($source);

        $rows = $grid->getRows();

        $this->assertEquals('&lt;xss&gt;', (string) $rows[0]->getValue('xss'));
    }

    public function testDisableAutoescape()
    {
        $grid = $this->getSampleGrid(collect([
            ['id' => 1, 'name' => '<b>1</b>', 'website' => 'http://4programmers.net']
        ]));

        $grid->getColumn('name')->setAutoescape(false);

        $rows = $grid->getRows();

        $this->assertEquals('<b>1</b>', (string) $rows[0]->getValue('name'));
    }

    private function getSampleGrid($collection = null)
    {
        if (empty($collection)) {
            $collection = collect([
                ['id' => 1, 'website' => 'http://4programmers.net']
            ]);
        }

        $grid = new Grid($this->gridHelper);
        $grid->addColumn('website', [
            'title' => 'Website',
            'decorators' => [
                new \Boduch\Grid\Decorators\Url()
            ]
        ]);
        $grid->addColumn('name', [
            'title' => 'Name'
        ]);
        $grid->addColumn('id', [
            'title' => 'ID',
            'clickable' => function ($row) {
                return '<a href="http://4programmers.net">' . $row['id'] . '</a>';
            }
        ]);

        $source = new Boduch\Grid\Source\CollectionSource($collection);
        $grid->setSource($source);

        return $grid;
    }
}
