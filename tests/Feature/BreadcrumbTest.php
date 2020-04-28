<?php

namespace Tests\Feature;

use Coyote\Services\Breadcrumb\Breadcrumb;
use Tests\TestCase;

class BreadcrumbTest extends TestCase
{
    /**
     * @var Breadcrumb
     */
    protected $breadcrumb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->breadcrumb = new Breadcrumb();
    }

    // tests
    public function testAddingElementToBreadcrumb()
    {
        $this->breadcrumb->push('Forum', '#');
        $this->assertCount(1, $this->breadcrumb);
    }

    public function testAddingMultipleElementsToBreadcrumb()
    {
        $this->breadcrumb->push([
            'Python'          => '#',
            'Dodaj nowy'      => '#'
        ]);

        $this->assertCount(2, $this->breadcrumb);
    }

    public function testRenderBreadcrumb()
    {
        $this->breadcrumb->push('Forum', '#');
        $this->assertInstanceOf('\Illuminate\View\View', $this->breadcrumb->render());
    }
}
