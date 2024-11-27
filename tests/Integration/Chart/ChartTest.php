<?php
namespace Tests\Integration\Chart;

use Coyote\Domain\View\Chart;
use PHPUnit\Framework\TestCase;

class ChartTest extends TestCase
{
    use Fixture\AssertsRender;

    /**
     * @test
     */
    public function empty()
    {
        $this->assertExpectedImage(new Chart([], [], [], 'chart'));
    }

    /**
     * @test
     */
    public function title()
    {
        $this->assertExpectedImage(new Chart([], [], [], 'chart'));
    }

    /**
     * @test
     */
    public function chart()
    {
        $this->assertExpectedImage(new Chart(['Foo', 'Bar'], [20, 30], [], 'chart'));
    }

    /**
     * @test
     */
    public function colors()
    {
        $this->assertExpectedImage(new Chart(
            ['Foo', 'Bar'],
            [20, 30],
            ['#ff9f40', '#ff6384'],
            'chart',
        ));
    }

    /**
     * @test
     */
    public function horizontal()
    {
        $this->assertExpectedImage(new Chart(
            ['Foo', 'Bar'],
            [20, 30],
            ['#ff9f40', '#ff6384'],
            'chart',
            horizontal:true,
        ));
    }

    /**
     * @test
     */
    public function multipleCharts(): void
    {
        $view = $this->renderedCharts([
            new Chart([], [], [], id:'first'),
            new Chart([], [], [], id:'second'),
        ]);
        $this->assertTrue($view->chartExists('first'));
        $this->assertTrue($view->chartExists('second'));
    }

    /**
     * @test
     */
    public function overflowingLabels()
    {
        $this->assertExpectedImage(new Chart(
            ['Father', 'Mother', 'Maiden', 'Crone', 'Warrior', 'Smith', 'Stranger'],
            [20, 30, 40, 50, 60, 70, 80],
            ['#ff9f40', '#ff6384'],
            'chart',
            horizontal:true,
        ));
    }

    /**
     * @test
     */
    public function baselineVertical()
    {
        $this->assertExpectedImage(new Chart(['Foo'], [20], ['#ff9f40'], 'chart', baseline:200));
    }

    /**
     * @test
     */
    public function baselineHorizontal()
    {
        $this->assertExpectedImage(new Chart(['Foo'], [20], ['#ff9f40'], 'chart', baseline:200, horizontal:true));
    }

    /**
     * @test
     */
    public function chartEmpty(): void
    {
        $chart = new Chart([], [], [], '');
        $this->assertTrue($chart->empty());
    }

    /**
     * @test
     */
    public function chartNotEmpty(): void
    {
        $chart = new Chart(['label'], [1], [], '');
        $this->assertFalse($chart->empty());
    }
}
