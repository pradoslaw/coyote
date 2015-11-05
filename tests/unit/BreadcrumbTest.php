<?php

use Coyote\Breadcrumb;

class BreadcrumbTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var Breadcrumb
     */
    protected $breadcrumb;

    protected function _before()
    {
        $this->breadcrumb = new Breadcrumb();
    }

    protected function _after()
    {
    }

    // tests
    public function testAddingElementToBreadcrumb()
    {
        $this->breadcrumb->push('Forum', '#');
        $this->assertEquals(1, count($this->breadcrumb));
    }

    public function testAddingMultipleElementsToBreadcrumb()
    {
        $this->breadcrumb->push([
            'Python'          => '#',
            'Dodaj nowy'      => '#'
        ]);

        $this->assertEquals(2, count($this->breadcrumb));
    }

    public function testRenderBreadcrumb()
    {
        $this->breadcrumb->push('Forum', '#');
        $this->assertInstanceOf('\Illuminate\View\View', $this->breadcrumb->render());
    }
}