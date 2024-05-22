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
        $this->assertExpectedImage(new Chart('', [], [], []));
    }

    /**
     * @test
     */
    public function title()
    {
        $this->assertExpectedImage(new Chart('Valar morghulis', [], [], []));
    }

    /**
     * @test
     */
    public function chart()
    {
        $this->assertExpectedImage(new Chart('Valar morghulis', ['Foo', 'Bar'], [20, 30], []));
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
        ));
    }
}
