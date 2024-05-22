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
        $this->assertExpectedImage(new Chart('', [], [], '#000000'));
    }

    /**
     * @test
     */
    public function title()
    {
        $this->assertExpectedImage(new Chart('Valar morghulis', [], [], '#000000'));
    }

    /**
     * @test
     */
    public function chart()
    {
        $this->assertExpectedImage(new Chart('Valar morghulis', ['Foo', 'Bar'], [20, 30], '#000000'));
    }

    /**
     * @test
     */
    public function color()
    {
        $this->assertExpectedImage(new Chart(
            'Valar morghulis',
            ['Foo', 'Bar'],
            [20, 30],
            '#ff9f40',
        ));
    }
}
