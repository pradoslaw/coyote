<?php
namespace Tests\Unit\Chart;

use Coyote\Domain\Chart;
use PHPUnit\Framework\TestCase;

class ChartTest extends TestCase
{
    use Fixture\AssertsRender;

    /**
     * @test
     */
    public function empty()
    {
        $this->assertExpectedImage(new Chart('', [], [], [], 'chart'));
    }

    /**
     * @test
     */
    public function title()
    {
        $this->assertExpectedImage(new Chart('Valar morghulis', [], [], [], 'chart'));
    }

    /**
     * @test
     */
    public function chart()
    {
        $this->assertExpectedImage(new Chart('Valar morghulis', ['Foo', 'Bar'], [20, 30], [], 'chart'));
    }

    /**
     * @test
     */
    public function colors()
    {
        $this->assertExpectedImage(new Chart(
            'Valar morghulis',
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
            'Valar morghulis',
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
        $browser = $this->newBrowserWithRenderedCharts([
            new Chart('', [], [], [], id:'first'),
            new Chart('', [], [], [], id:'second'),
        ]);
        $this->assertTrue($this->chartExists($browser, 'first'));
        $this->assertTrue($this->chartExists($browser, 'second'));
    }
}
